<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class HavQuadrant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['employee_id', 'assessment_score', 'performance_score', 'quadrant'];

    // Many-to-One: Hav -> Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    function generateHavQuadrant($employee_id, $assessment, $performance)
    {
        // Tentukan rentang X
        if ($assessment >= 1.0 && $assessment < 2.5) {
            $col = 0;
        } elseif ($assessment >= 2.5 && $assessment <= 3.0) {
            $col = 1;
        } elseif ($assessment > 3.0 && $assessment <= 3.5) {
            $col = 2;
        } elseif ($assessment > 3.5 && $assessment <= 5.0) {
            $col = 3;
        } else {
            Log::info('assessment skor :' . $assessment);
            throw new \Exception("Nilai Assessment tidak valid. Mohon periksa kembali input Anda.");
        }

        // Tentukan rentang Y
        if ($performance >= 1 && $performance <= 11) {
            $row = 3;  // Baris "1 - 11" => Indeks 3 (Baris paling bawah)
        } elseif ($performance >= 12 && $performance <= 15) {
            $row = 2;  // Baris "12 - 15" => Indeks 2
        } elseif ($performance >= 16 && $performance <= 20) {
            $row = 1;  // Baris "16 - 20" => Indeks 1
        } elseif ($performance >= 21 && $performance <= 24) {
            $row = 0;  // Baris "21 - 24" => Indeks 0 (Baris paling atas)
        } else {
            Log::info('performance skor :' . $performance);
            throw new \Exception("Nilai Performance tidak valid. Mohon periksa kembali input Anda.");
        }

        // Matriks nilai dalam tabel
        $matrix = [
            [13, 7, 3, 1],
            [14, 8, 4, 2],
            [15, 9, 6, 5],
            [16, 12, 11, 10]
        ];
        // Insert or update the hav_quadrant table
        $havQuadrant = HavQuadrant::updateOrCreate(
            ['employee_id' => $employee_id],
            [
                'assessment_score' => $assessment,
                'performance_score' => $performance,
                'quadrant' => $matrix[$row][$col]
            ]
        );

        Log::info($havQuadrant);

        // Mengembalikan nilai sesuai posisi X dan Y
        return $matrix[$row][$col];
    }

    // Get average from 3 last performance appraisal history by employee_id
    public function getLastPerformanceAppraisal($employee_id, $year)
    {
        $performance = PerformanceAppraisalHistory::where('employee_id', $employee_id)
            ->whereIn(DB::raw('YEAR(date)'), [$year - 1, $year - 2, $year - 3])
            ->get();

        if ($performance->isEmpty()) {
            return 1; // Return 0 if no performance appraisal history found
        }

        foreach ($performance as $key => $value) {
            $score = PerformanceMaster::select('score')->where('code', $value->score)->first();
            $scores[$key] = $score->score;
        }

        $getSum = array_sum($scores);
        return $getSum;
    }

    // Get average Assessment from Alc score
    public function updateHavFromAssessment($employee_id, $year, $alc1, $alc2, $alc3, $alc4, $alc5, $alc6, $alc7, $alc8)
    {
        // Ambil grade dari karyawan
        $emp = Employee::select('grade')->find($employee_id);
        $grade = $emp?->grade;

        // Cek apakah termasuk golongan 4-6
        $gradeGroup = (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT); // Ambil angka dari grade, contoh: "4A" => 4

        if ($gradeGroup >= 4 && $gradeGroup <= 6) {
            // Golongan 4-6: hitung rata-rata dari ALC 3,4,5,7,8 (tanpa bobot, rata-rata biasa)
            $average = ($alc3 + $alc4 + $alc5 + $alc7 + $alc8) / 5;
        } else {
            // Selain golongan 4-6: gunakan bobot normal
            $average = ($alc1 * 0.15) + ($alc2 * 0.15) + ($alc3 * 0.10) + ($alc4 * 0.10) + ($alc5 * 0.10)
                + ($alc6 * 0.15) + ($alc7 * 0.10) + ($alc8 * 0.15);
        }

        $pkScore = $this->getLastPerformanceAppraisal($employee_id, $year);
        $quadrant = $this->generateHavQuadrant($employee_id, $average, $pkScore);
        return $this->generateHavQuadrant($employee_id, $average, $pkScore);
    }


    //Update Hav Quadrant from Performance Appraisal
    public function updateHavFromPerformance($employee_id, $year)
    {
        $getHavQuadrant = HavQuadrant::where('employee_id', $employee_id)->first();
        $pkScore = $this->getLastPerformanceAppraisal($employee_id, $year);
        $this->generateHavQuadrant($employee_id, $getHavQuadrant->assessment_score ?? 1, $pkScore);
    }


    public function getValidatedPerformanceScores($employee_id, $year)
    {
        $years = [$year, $year - 1, $year - 2];
        $missingYears = [];

        // Ambil semua data appraisal 3 tahun
        $performance = PerformanceAppraisalHistory::where('employee_id', $employee_id)
            ->whereIn(DB::raw('YEAR(date)'), $years)
            ->get();

        // Kelompokkan berdasarkan tahun
        $grouped = $performance->groupBy(function ($item) {
            return date('Y', strtotime($item->date));
        });

        // Cek tahun yang tidak ditemukan
        foreach ($years as $y) {
            if (!isset($grouped[$y])) {
                $missingYears[] = $y;
            }
        }

        if (!empty($missingYears)) {
            return 'Data performace appraisal tidak ditemukan untuk tahun: ' . implode(', ', $missingYears);
        }

        // Validasi dan hitung score
        $scores = [];
        foreach ($performance as $value) {
            $scoreModel = PerformanceMaster::select('score')->where('code', $value->score)->first();

            if (!$scoreModel || $scoreModel->score === null) {
                return 'Score tidak ditemukan atau kosong untuk appraisal pada tanggal: ' . $value->date;
            }

            $scores[] = $scoreModel->score;
        }

        return array_sum($scores);
    }
    public function hav()
    {
        return $this->belongsTo(Hav::class, 'employee_id', 'employee_id');
    }
}
