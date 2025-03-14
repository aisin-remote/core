<?php

namespace App\Http\Controllers;

use \DB;
use App\Models\Alc;
use App\Models\Assessment;
use App\Models\DetailAssessment;
use App\Models\Employee;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Menampilkan form create.
     *
     *
     */
    public function index(Request $request)
    {
        // Ambil semua employee untuk dropdown
        $employees = Employee::all();
        $employeesWithAssessments = Employee::whereHas('assessments')->get();
        $alcs = Alc::all();

        // Ambil assessment terbaru per employee
        $assessments = Assessment::with(['employee', 'alc'])
            ->whereHas('employee')
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('npk', 'like', '%' . $request->search . '%');
                });
            })
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('assessments')
                    ->groupBy('employee_id');
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->paginate(10);

        return view('website.assessment.index', compact('assessments', 'employees', 'alcs', 'employeesWithAssessments'));
    }
    public function show($employee_id)
    {
        // Ambil data karyawan berdasarkan ID
        $employee = Employee::with('assessments')->findOrFail($employee_id);

        // Ambil assessment dan sertakan kolom `upload`
        $assessments = Assessment::where('employee_id', $employee_id)
            ->selectRaw('date, MAX(id) as id, employee_id, MAX(upload) as upload') // Tambahkan `upload`
            ->groupBy('date', 'employee_id') // Grouping harus mencakup employee_id
            ->orderBy('date', 'desc')
            ->get();

        return view('website.assessment.show', compact('employee', 'assessments'));
    }

    public function showByDate($assessment_id, $date)
    {
        // Ambil assessment berdasarkan ID
        $assessment = Assessment::findOrFail($assessment_id);

        // Ambil employee dari assessment (pastikan kolom employee_id ada di tabel assessments)
        $employee = Employee::findOrFail($assessment->employee_id);

        // Ambil data detail_assessment dengan alc (menggunakan Eloquent)
        $assessments = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment_id)
            ->get();

        // Debugging untuk memastikan data tidak null
        if ($assessments->isEmpty()) {
            return back()->with('error', 'Tidak ada data assessment pada tanggal tersebut.');
        }

        // Ambil detail menggunakan join untuk mendapatkan alc_name dan score dari detail_assessment
        $details = \DB::table('detail_assessments')
            ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
            ->where('detail_assessments.assessment_id', $assessment_id)
            ->select(
                'detail_assessments.*',
                'alc.name as alc_name', // Ambil nama ALC dari tabel alc
                'detail_assessments.score' // Ambil score dari detail_assessment
            )
            ->get();

        return view('website.assessment.detail', compact('employee', 'assessments', 'date', 'details'));
    }


    public function create()
    {
        $employees = Employee::all(); // Ambil semua employee
        $alcs = Alc::all(); // Ambil semua alc_id

        return view('assessments.create', compact('employees', 'alcs'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'alc_ids' => 'required|array',
            'alc_ids.*' => 'exists:alc,id',
            'scores' => 'nullable|array',
            'scores.*' => 'nullable|string|max:2',
            'strenght' => 'nullable|array',
            'weakness' => 'nullable|array',
        ]);

        // Simpan file jika ada
        $filePath = null;
        if ($request->hasFile('upload')) {
            $filePath = 'uploads/assessments/' . $request->file('upload')->hashName();
            $request->file('upload')->storeAs('public', $filePath);
        }

        // Simpan data utama ke tabel assessments
        $assessment = Assessment::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'upload' => $filePath,
        ]);


        // Simpan data detail ke tabel assessment_details
        $assessmentDetails = [];
        foreach ($request->alc_ids as $index => $alc_id) {
            DB::table('detail_assessments')
                ->updateOrInsert(
                    [
                        'assessment_id' => $assessment->id,
                        'alc_id' => $alc_id
                    ],
                    [
                        'score' => $request->scores[$alc_id] ?? "0",  // Ambil nilai score berdasarkan ALC ID
                        'strength' => $request->strength[$alc_id] ?? "", // Ambil nilai strength berdasarkan ALC ID
                        'weakness' => $request->weakness[$alc_id] ?? "",
                        'updated_at' => now()
                    ]
                );
        }


        // Simpan batch data ke database
        return response()->json([
            'success' => true,
            'message' => 'Data assessment berhasil disimpan.',
            'assessment' => $assessment,
            'assessment_details' => $assessmentDetails,
        ]);
    }
    public function getAssessmentDetail($employee_id)
    {
        // 🔹 Cari assessment terbaru dari employee
        $assessment = Assessment::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc') // Ambil yang paling baru diinput
            ->first();

        if (!$assessment) {
            return response()->json(['error' => 'Data assessment tidak ditemukan'], 404);
        }

        // 🔹 Ambil data employee terbaru
        $employee = Employee::findOrFail($employee_id);

        // 🔹 Ambil detail assessment dengan ALC
        $details = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment->id)
            ->get();

        // 🔹 Pisahkan Strength dan Weakness yang memiliki nilai
        $strengths = $details->filter(fn($d) => !empty($d->strength))->values();
        $weaknesses = $details->filter(fn($d) => !empty($d->weakness))->values();

        return response()->json([
            'employee' => $employee,
            'assessment' => $assessment,
            'date' => $assessment->date,
            'details' => $details,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ]);
    }


}
