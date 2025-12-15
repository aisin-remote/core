<?php

namespace App\Services\Excel;

use App\Models\Assessment;
use App\Models\Development;
use App\Models\DevelopmentOne;
use App\Models\DevelopmentApprovalStep;
use App\Models\Employee;
use App\Models\Idp;
use App\Models\IdpApproval;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
        $sheet       = $spreadsheet->getActiveSheet();

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

        $strengths  = [];
        $weaknesses = [];

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->strength)) {
                // baris 1: nama ALC, baris 2: isi strength
                $strengths[] = " - " . $detail->alc_name . "\n  ";
            }

            if (!empty($detail->weakness)) {
                // baris 1: nama ALC, baris 2: isi weakness
                $weaknesses[] = " - " . $detail->alc_name . "\n  ";
            }
        }

        $strengthText = implode("\n\n", $strengths);  // \n\n biar ada jarak antar ALC
        $weaknessText = implode("\n\n", $weaknesses);

        $sheet->setCellValue('B' . $startRow, $strengthText);
        $sheet->setCellValue('F' . $startRow, $weaknessText);

        // rata kiri + wrap text
        $sheet->getStyle('B' . $startRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B' . $startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle('F' . $startRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('F' . $startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);


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

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->weakness)) {
                // baris 1: nama ALC, baris 2: detail weakness (development area)
                $cellText = $detail->alc_name . "\n" . $detail->weakness;

                $sheet->setCellValue('C' . $startRow, $cellText);

                $sheet->getStyle('C' . $startRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('C' . $startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

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
         * PASANG STAMP (signature images) KE TEMPLATE - BAGIAN IDP (kiri)
         *
         * Posisi sel IDP:
         *  - Pemilik IDP -> C48
         *  - Atasan 1     -> F48
         *  - Atasan 2     -> K48
         */

        $stampMap = [
            'director'  => public_path('assets/media/stamp/DIR.png'),
            'gm'        => public_path('assets/media/stamp/GM.png'),
            'mgr'       => public_path('assets/media/stamp/MGR.png'),
            'vpd'       => public_path('assets/media/stamp/VPD.png'),
            'president' => public_path('assets/media/stamp/PD.png'),
        ];

        $defaultOwnerStamp = public_path('assets/media/stamp/EMP.png');

        // Cari stamp berdasarkan posisi (untuk ATASAN, bukan owner)
        $findStampByPosition = function (?string $position) use ($stampMap) {
            if (!$position) return null;

            $pos = strtolower($position);

            $keywords = [
                'president' => ['president', 'pd'],
                'director'  => ['director', 'dir'],
                'gm'        => ['gm', 'general manager'],
                'vpd'       => ['vpd', 'vice president'],
                'mgr'       => ['manager', 'mgr', 'supervisor', 'spv', 'coordinator'],
            ];

            foreach ($keywords as $key => $aliases) {
                foreach ($aliases as $alias) {
                    if (strpos($pos, $alias) !== false && isset($stampMap[$key]) && file_exists($stampMap[$key])) {
                        return $stampMap[$key];
                    }
                }
            }

            return null;
        };

        // Ambil 1 IDP utama (kalau ada) untuk tahu status & assessment_id
        $idpForStamp = $idpRecords->first();
        $statusIdp   = null;

        if ($idpForStamp) {
            $statusIdp = $idpForStamp->status ?? null;
        }

        // ========== OWNER STAMP (IDP) ==========
        $ownerStampPath = null;
        if (file_exists($defaultOwnerStamp)) {
            $ownerStampPath = $defaultOwnerStamp;
        }

        // ========== APPROVER LEVEL 1 & 2 (IDP) ==========
        $approver1StampPath = null;
        $approver2StampPath = null;

        $approvalLevel1 = null;
        $approvalLevel2 = null;

        if ($idpForStamp) {
            $approvalLevel1 = IdpApproval::where('level', 1)
                ->where(function ($q) use ($idpForStamp) {
                    $q->where('assessment_id', $idpForStamp->assessment_id)
                        ->orWhere('idp_id', $idpForStamp->id);
                })
                ->latest('approved_at')
                ->first();

            $approvalLevel2 = IdpApproval::where('level', 2)
                ->where(function ($q) use ($idpForStamp) {
                    $q->where('assessment_id', $idpForStamp->assessment_id)
                        ->orWhere('idp_id', $idpForStamp->id);
                })
                ->latest('approved_at')
                ->first();

            if ($approvalLevel1 && $approvalLevel1->approver) {
                $approver1StampPath = $findStampByPosition($approvalLevel1->approver->position ?? null);
            }

            if ($approvalLevel2 && $approvalLevel2->approver) {
                $approver2StampPath = $findStampByPosition($approvalLevel2->approver->position ?? null);
            }
        }

        // Helper untuk memasang gambar pada koordinat sel
        $placeStamp = function (?string $imagePath, string $cellCoordinate, $worksheet) {
            if (!$imagePath || !file_exists($imagePath)) {
                return;
            }

            $drawing = new Drawing();
            $drawing->setPath($imagePath);
            $drawing->setCoordinates($cellCoordinate);

            // Ukuran & offset bisa disesuaikan dengan template
            $drawing->setHeight(190);
            $drawing->setOffsetX(50);
            $drawing->setOffsetY(20);

            $drawing->setWorksheet($worksheet);
        };

        /*
         * LOGIKA STATUS → STAMP (IDP)
         * Asumsi:
         *  - status >= 1 : tampilkan stamp pemilik (C48)
         *  - status >= 2 : tampilkan stamp atasan 1 (F48)
         *  - status >= 3 : tampilkan stamp atasan 2 (K48)
         */

        if ($statusIdp === null) {
            // Tidak ada IDP → minimal pasang owner kalau ada
            $placeStamp($ownerStampPath, 'C48', $sheet);
        } else {
            if ($statusIdp >= 1) {
                $placeStamp($ownerStampPath, 'C48', $sheet);
            }

            if ($statusIdp >= 2 && $approver1StampPath) {
                $placeStamp($approver1StampPath, 'F48', $sheet);
            }

            if ($statusIdp >= 3 && $approver2StampPath) {
                $placeStamp($approver2StampPath, 'K48', $sheet);
            }
        }

        /*
         * NAMA DI BAWAH STAMP BAGIAN IDP
         *  - Owner  -> B52
         *  - Level1 -> E52
         *  - Level2 -> I52
         */

        // Owner name (selalu dari $employee)
        $sheet->setCellValue('B52', $employee->name ?? '-');

        // Approver level 1 name (jika ada), tulis '-' jika tidak ada
        $approver1Name = '-';
        if (!empty($approvalLevel1) && !empty($approvalLevel1->approver)) {
            $approver1Name = $approvalLevel1->approver->name ?? '-';
        }
        $sheet->setCellValue('E52', $approver1Name);

        // Approver level 2 name (jika ada), tulis '-' jika tidak ada
        $approver2Name = '-';
        if (!empty($approvalLevel2) && !empty($approvalLevel2->approver)) {
            $approver2Name = $approvalLevel2->approver->name ?? '-';
        }
        $sheet->setCellValue('I52', $approver2Name);

        /*
         * =========================================================
         *  STAMP BAGIAN DEVELOPMENT (KANAN)
         *  Posisi:
         *    - Owner (employee)      -> O48
         *    - Approver tingkat 1    -> R48
         *    - Approver tingkat 2    -> U48
         *  Nama di bawah stamp:
         *    - Owner   -> O52
         *    - Level1  -> R52
         *    - Level2  -> U52
         * =========================================================
         */

        // Ambil 1 record ONE-YEAR terbaru untuk dasar status & approval
        $devOneForStamp = DevelopmentOne::where('employee_id', $employeeId)->latest()->first();
        $statusDev      = $devOneForStamp->status ?? null;

        // Ambil step approval untuk development (pakai DevelopmentApprovalStep)
        $devSteps = collect();
        if ($devOneForStamp) {
            $devSteps = DevelopmentApprovalStep::with('actor')
                ->where('development_one_id', $devOneForStamp->id)
                ->where('status', 'done')        // hanya step yang sudah selesai
                ->orderBy('step_order')
                ->get();
        }

        // Tentukan approver level 1 & 2 dari step yang "done"
        $devApprover1   = $devSteps->get(0); // step done pertama
        $devApprover2   = $devSteps->get(1); // step done kedua
        $devApprover1StampPath = $devApprover1 && $devApprover1->actor
            ? $findStampByPosition($devApprover1->actor->position ?? null)
            : null;
        $devApprover2StampPath = $devApprover2 && $devApprover2->actor
            ? $findStampByPosition($devApprover2->actor->position ?? null)
            : null;

        // Mapping status DevelopmentOne → level stamp
        // Silakan adjust kalau di DB-mu beda.
        $devLevel = 0;
        if ($statusDev) {
            switch (strtolower($statusDev)) {
                case 'draft':
                case 'submitted':
                    $devLevel = 1; // hanya owner
                    break;
                case 'checked':
                    $devLevel = 2; // owner + approver1
                    break;
                case 'approved':
                    $devLevel = 3; // owner + approver1 + approver2
                    break;
                default:
                    $devLevel = 0;
                    break;
            }
        }

        // Pasang stamp development
        if ($devLevel >= 1 && $ownerStampPath) {
            $placeStamp($ownerStampPath, 'O48', $sheet);
        }

        if ($devLevel >= 2 && $devApprover1StampPath) {
            $placeStamp($devApprover1StampPath, 'R48', $sheet);
        }

        if ($devLevel >= 3 && $devApprover2StampPath) {
            $placeStamp($devApprover2StampPath, 'U48', $sheet);
        }

        // Nama di bawah stamp development
        $sheet->setCellValue('O52', $employee->name ?? '-');

        $devApprover1Name = '-';
        if ($devApprover1 && $devApprover1->actor) {
            $devApprover1Name = $devApprover1->actor->name ?? '-';
        }
        $sheet->setCellValue('R52', $devApprover1Name);

        $devApprover2Name = '-';
        if ($devApprover2 && $devApprover2->actor) {
            $devApprover2Name = $devApprover2->actor->name ?? '-';
        }
        $sheet->setCellValue('U52', $devApprover2Name);

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
