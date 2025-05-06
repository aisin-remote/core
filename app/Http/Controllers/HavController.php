<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Alc;
use App\Models\Hav;
use App\Models\Employee;
use App\Models\HavDetail;
use App\Models\Assessment;
use App\Models\HavQuadrant;
use App\Models\KeyBehavior;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HavDetailKeyBehavior;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Yajra\DataTables\Facades\DataTables;
use App\Models\PerformanceAppraisalHistory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Database\Events\TransactionBeginning;

class HavController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Employee List';
        // dd($assessments);
        $havGrouped = HavQuadrant::with('employee')->get()->groupBy('quadrant');

        // Quadrant ID => Judul
        $titles = [
            13 => 'Maximal Contributor',
            7  => 'Top Performer',
            3  => 'Future Star',
            1  => 'Star',
            14 => 'Contributor',
            8  => 'Strong Performer',
            4  => 'Potential Candidate',
            2  => 'Future Star',
            15 => 'Minimal Contributor',
            9  => 'Career Person',
            6  => 'Candidate',
            5  => 'Raw Diamond',
            16 => 'Dead Wood',
            12 => 'Problem Employee',
            11 => 'Unfit Employee',
            10 => 'Most Unfit Employee',
        ];

        $orderedHavGrouped = collect(array_keys($titles))->mapWithKeys(function ($quadrantId) use ($havGrouped) {
            return [$quadrantId => $havGrouped[$quadrantId] ?? collect()];
        });

        return view('website.hav.index', compact('orderedHavGrouped', 'titles'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $title = 'Add Employee';
        $employees = Assessment::with('employee')
            ->whereHas('employee', function ($query) {
                $query->where('company_name', 'AIIA');
            })
            ->get()
            ->unique('employee_id')
            ->values(); // reset indeks agar rapi

        return view('website.hav.list', compact('title', 'employees'));
    }

    public function ajaxList(Request $request)
    {
        $data = Hav::with('employee')->get(); // Pastikan relasi 'employee' ada

        return DataTables::of($data)
            ->addColumn('npk', fn($row) => $row->employee->npk ?? '-')
            ->addColumn('nama', fn($row) => $row->employee->name ?? '-')
            ->addColumn('status', fn($row) => $row->status)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateCreate($id)
    {
        // $createHav = new Hav();
        // $createHav->employee_id = $id;
        // $createHav->save();

        // $hav_id = $createHav->id;
        // $alc = Alc::all();

        // foreach ($alc as $alc) {
        //     $createHavDetail = new HavDetail();
        //     $createHavDetail->alc_id = $alc->id;
        //     $createHavDetail->hav_id = $hav_id;
        //     $createHavDetail->score = 0;
        //     $createHavDetail->evidence = '';
        //     $createHavDetail->save();

        //     $keyBehaviors = KeyBehavior::where('alc_id', $alc->id)->get();
        //     foreach ($keyBehaviors as $keyBehavior) {
        //         $createHavDetailKeyBehavior = new HavDetailKeyBehavior();
        //         $createHavDetailKeyBehavior->hav_detail_id = $createHavDetail->id;
        //         $createHavDetailKeyBehavior->key_behavior_id = $keyBehavior->id;
        //         $createHavDetailKeyBehavior->score = 0;
        //         $createHavDetailKeyBehavior->save();
        //     }
        // }


        // $title = 'Add Employee';
        // $employees = Employee::all();
        return redirect()->route('hav.update', $id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listCreate()
    {
        $title = 'Add Employee';
        $employees = Assessment::with('employee')
            ->whereHas('employee', function ($query) {
                $query->where('company_name', 'AII');
            })
            ->get();

        return view('website.hav.list-create', compact('title', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $title = 'Add Employee';
        $hav = Assessment::with(['employee', 'alc', 'details'])
            ->whereHas('employee', function ($query) use ($id) {
                return $query->where('id', $id);
            })
            ->first();

        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();

        return view('website.hav.create', compact('title', 'hav', 'performanceAppraisals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRating(Request $request)
    {
        $request->validate([
            'key_behavior_id' => 'required|exists:hav_detail_key_behaviors,key_behavior_id',
            'hav_detail_id' => 'required|exists:hav_detail_key_behaviors,hav_detail_id',
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $ratingUpdate = HavDetailKeyBehavior::where([
            'key_behavior_id' => $request->key_behavior_id,
            'hav_detail_id' => $request->hav_detail_id
        ])->first();

        if ($ratingUpdate) {
            $ratingUpdate->score = $request->rating;
            $ratingUpdate->save();

            // ✅ Hitung rata-rata dari semua score HavDetailKeyBehavior yang terkait dengan HavDetail ini
            $averageScore = HavDetailKeyBehavior::where('hav_detail_id', $request->hav_detail_id)
                ->avg('score'); // Menghitung rata-rata nilai

            // ✅ Update score di HavDetail dengan nilai rata-rata
            HavDetail::where('id', $request->hav_detail_id)
                ->update(['score' => $averageScore]);

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'new_average' => floatval($averageScore) // Kirim rata-rata terbaru ke frontend
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Record not found'], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        // $path = storage_path('app/templates/HAV_Summary.xlsx');
        // $spreadsheet = IOFactory::load($path);
        // $sheet = $spreadsheet->getActiveSheet();

        // $assessments = Assessment::with(['employee', 'details'])->get();
        // $startRow = 13;

        // foreach ($assessments as $i => $a) {
        //     $row = $startRow + $i;
        //     $details = $a->details->keyBy('alc_id');

        //     $sheet->setCellValue("A{$row}", $a->employee->npk ?? '');
        //     $sheet->setCellValue("B{$row}", $a->date);
        //     $sheet->setCellValue("C{$row}", $a->description);

        //     $col = 'D';
        //     for ($j = 1; $j <= 8; $j++) {
        //         $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '');
        //         $col++;
        //         $sheet->setCellValue("{$col}{$row}", $details[$j]->strength ?? '');
        //         $col++;
        //         $sheet->setCellValue("{$col}{$row}", $details[$j]->weakness ?? '');
        //         $col++;
        //     }
        // }

        // $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        // $filename = 'exported_hav_manual.xlsx';
        // $writer->save(public_path($filename));

        // return response()->download(public_path($filename))->deleteFileAfterSend(true);


        // $templatePath = storage_path('app/templates/HAV_Summary.xlsx');
        $templatePath = public_path('assets/file/HAV_Summary.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $employees = Employee::with([
            'assessments.details',
            'havQuadrants' => function ($q) {
                $q->orderByDesc('created_at');
            },
            'performanceAppraisalHistories' => function ($q) {
                $q->orderBy('date');
            }
        ])->whereHas('havQuadrants')->get();

        $startRow = 13;

        foreach ($employees as $i => $emp) {
            $row = $startRow + $i;
            $assessment = $emp->assessments->sortByDesc('created_at')->first();
            $details = $assessment ? $assessment->details->keyBy('alc_id') : collect();

            // Total Score
            $totalScore = $details->sum(fn($d) => floatval($d->score ?? 0));
            $totalScorePercent = $totalScore ? round(($totalScore / (8 * 5)) * 100, 1) . '%' : '0%';

            // HAV Quadrant
            $hav = $emp->havQuadrants->first();
            $quadrant = $hav->quadrant ?? null;

            // Performance Appraisal 3 tahun terakhir
            $appraisals = $emp->performanceAppraisalHistories
                ->sortByDesc('date')
                ->take(3)
                ->sortBy('date')
                ->values();

            // Mapping kolom (semua geser 1 ke kanan)
            $sheet->setCellValue("B{$row}", $emp->npk);
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->function);
            $sheet->setCellValue("E{$row}", $emp->foundation_group); // Divisi
            $sheet->setCellValue("F{$row}", $emp->company_group); // Departemen
            $sheet->setCellValue("G{$row}", Carbon::parse($emp->birthday_date)->age ?? null); // Usia
            $sheet->setCellValue("H{$row}", $emp->grade); // Sub Gol
            $sheet->setCellValue("I{$row}", $emp->working_period); // Masa kerja

            // ALCs
            $col = 'J';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '');
                $col++;
            }

            $sheet->setCellValue("R{$row}", $totalScore);
            $sheet->setCellValue("S{$row}", $totalScorePercent);

            // Kolom Appraisal 3 Tahun (T, U, V)
            $sheet->setCellValue("T{$row}", $appraisals[0]->score ?? '');
            $sheet->setCellValue("U{$row}", $appraisals[1]->score ?? '');
            $sheet->setCellValue("V{$row}", $appraisals[2]->score ?? '');

            // HAV terakhir (W, X, Y)
            $sheet->setCellValue("W{$row}", $hav->assessment_score ?? '');
            $sheet->setCellValue("X{$row}", $hav->performance_score ?? '');
            $sheet->setCellValue("Y{$row}", $quadrant);

            // Breakdown score terbaru (Z - AG)
            $breakdownCol = 'Z';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$breakdownCol}{$row}", $details[$j]->score ?? '');
                $breakdownCol++;
            }
        }


        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'HAV_Summary_Exported.xlsx';
        $writer->save(public_path($filename));

        return response()->download(public_path($filename))->deleteFileAfterSend(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
