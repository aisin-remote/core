<?php

namespace App\Services\Excel;

use App\Models\Assessment;
use App\Models\Development;
use App\Models\DevelopmentOne;
use App\Models\Employee;
use App\Models\Idp;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class IdpExportService
{
    /**
     * Generate file Excel IDP dan return path file sementara.
     *
     * @param  int       $employeeId
     * @param  int|null  $assessmentId  (optional, kalau null pakai assessment terakhir)
     * @return string  full path ke file sementara
     *
     * @throws \Exception
     */
    public function exportTemplate(int $employeeId, ?int $assessmentId = null): string
    {
        $filePath = public_path('assets/file/idp_template.xlsx');

        if (!file_exists($filePath)) {
            throw new \Exception('File template tidak ditemukan.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            throw new \Exception('Employee tidak ditemukan.');
        }

        // Assessment terakhir (dipakai untuk header)
        $assessment = Assessment::where('employee_id', $employeeId)->latest()->first();
        if (!$assessment) {
            throw new \Exception('Assessment tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Header data karyawan & assessment
        $sheet->setCellValue('H3', $employee->name);
        $sheet->setCellValue('K3', $employee->npk);
        $sheet->setCellValue('R3', $employee->position);
        $sheet->setCellValue('R4', $employee->position);
        $sheet->setCellValue('R5', $employee->birthday_date);
        $sheet->setCellValue('R6', $employee->aisin_entry_date);
        $sheet->setCellValue('R7', $assessment->date);
        $sheet->setCellValue('H6', $employee->grade);
        $sheet->setCellValue('H5', $employee->department_id);

        /*
         * STRENGTH & WEAKNESS (row 13)
         */
        $startRow = 13;

        $latestAssessment = DB::table('assessments')
            ->where('employee_id', $employeeId)
            ->latest('created_at')
            ->first();

        if (!$latestAssessment) {
            throw new \Exception('Assessment tidak ditemukan untuk employee ini.');
        }

        $assessmentDetails = DB::table('detail_assessments')
            ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
            ->where('detail_assessments.assessment_id', $latestAssessment->id)
            ->select('detail_assessments.*', 'alc.name as alc_name')
            ->get();

        $strengths = [];
        $weaknesses = [];

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->strength)) {
                $strengths[] = " - " . $detail->alc_name;
            }
            if (!empty($detail->weakness)) {
                $weaknesses[] = " - " . $detail->alc_name;
            }
        }

        $strengthText  = implode("\n", $strengths);
        $weaknessText  = implode("\n", $weaknesses);

        $sheet->setCellValue('B' . $startRow, $strengthText);
        $sheet->setCellValue('F' . $startRow, $weaknessText);

        /*
         * DEVELOPMENT NEED BASED ON WEAKNESS (col C, start row 33)
         */
        $startRow = 33;

        // Pakai assessmentId dari parameter kalau ada, kalau tidak ambil yang terakhir
        $assessmentId = $assessmentId ?: Assessment::where('employee_id', $employeeId)->latest()->value('id');

        if (!$assessmentId) {
            throw new \Exception('Assessment ID tidak ditemukan.');
        }

        $assessmentDetails = DB::table('detail_assessments')
            ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
            ->where('detail_assessments.assessment_id', $latestAssessment->id)
            ->select('detail_assessments.*', 'alc.name as alc_name')
            ->get();

        // Versi original kodenya dua kali isi kolom C dengan pola yang mirip.
        // Di sini tetap kita ikuti struktur aslinya.

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->weakness)) {
                $sheet->setCellValue('C' . $startRow, $detail->alc_name . " - " . $detail->weakness);
                $startRow += 2;
            }
        }

        // Reset dan isi lagi (sesuai kode awal)
        $startRow = 33;

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->weakness)) {
                $sheet->setCellValue('C' . $startRow, $detail->alc_name);
                $startRow += 2;
            }
        }

        /*
         * IDP (col D/E/H/K, start row 33)
         */
        $startRow = 33;

        $assessmentId = $assessmentId ?: Assessment::where('employee_id', $employeeId)->latest()->value('id');

        if (!$assessmentId) {
            throw new \Exception('Assessment ID tidak ditemukan.');
        }

        $idpRecords = Idp::where('assessment_id', $assessmentId)->get();

        foreach ($idpRecords as $idp) {
            $sheet->setCellValue('E' . $startRow, $idp->development_program ?? "-");
            $sheet->setCellValue('D' . $startRow, $idp->category ?? "-");
            $sheet->setCellValue('H' . $startRow, $idp->development_target ?? "-");
            $sheet->setCellValue('K' . $startRow, $idp->date ?? "-");

            $startRow += 2;
        }

        /*
         * MID YEAR DEVELOPMENT (col O/R/U, start row 13)
         */
        $startRow = 13;

        $assessmentId = $assessmentId ?: Assessment::where('employee_id', $employeeId)->latest()->value('id');

        if (!$assessmentId) {
            throw new \Exception('Assessment ID tidak ditemukan.');
        }

        $midYearRecords = Development::where('employee_id', $employeeId)->get();

        foreach ($midYearRecords as $record) {
            $sheet->setCellValue('O' . $startRow, $record->development_program ?? "-");
            $sheet->setCellValue('R' . $startRow, $record->development_achievement ?? "-");
            $sheet->setCellValue('U' . $startRow, $record->next_action ?? "-");

            $startRow++;
        }

        /*
         * ONE YEAR DEVELOPMENT (col O/R, start row 33)
         */
        $startRow = 33;

        $assessmentId = $assessmentId ?: Assessment::where('employee_id', $employeeId)->latest()->value('id');

        if (!$assessmentId) {
            throw new \Exception('Assessment ID tidak ditemukan.');
        }

        $oneYearRecords = DevelopmentOne::where('employee_id', $employeeId)->get();

        foreach ($oneYearRecords as $record) {
            $sheet->setCellValue('O' . $startRow, $record->development_program ?? "-");
            $sheet->setCellValue('R' . $startRow, $record->evaluation_result ?? "-");

            $startRow += 2;
        }

        /*
         * SIMPAN FILE SEMENTARA & RETURN PATH
         */
        $tempDir = storage_path('app/public/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $fileName = 'IDP_' . str_replace(' ', '_', $employee->name) . '.xlsx';
        $tempPath = $tempDir . '/' . $fileName;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return $tempPath;
    }
}
