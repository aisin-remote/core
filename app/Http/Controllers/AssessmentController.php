<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Alc;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\DetailAssessment;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Request;

class AssessmentController extends Controller
{
    /**
     * Menampilkan form create.
     *
     *
     */
    public function getSubordinates($employeeId, $processedIds = [])
    {
        // Cegah infinite loop dengan memeriksa apakah ID sudah diproses sebelumnya
        if (in_array($employeeId, $processedIds)) {
            return collect(); // Kembalikan collection kosong untuk menghindari loop
        }

        // Tambahkan ID saat ini ke daftar yang sudah diproses
        $processedIds[] = $employeeId;

        // Ambil hanya bawahan langsung (bukan atasan)
        $employees = Employee::where('supervisor_id', $employeeId)->get();
        $subordinates = collect($employees);

        // Lanjutkan rekursi untuk mendapatkan semua bawahan di level lebih dalam
        foreach ($employees as $employee) {
            $subordinates = $subordinates->merge($this->getSubordinates($employee->id, $processedIds));
        }

        return $subordinates;
    }

    public function index(Request $request, $company = null)
    {
        $user = auth()->user();
        $title = 'Assessment';

        // Jika HRD, bisa melihat semua employee dan assessment dalam satu perusahaan (jika ada filter company)
        if ($user->role === 'HRD') {
            $employees = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
                ->when($company, fn($query) => $query->where('company_name', $company))
                ->get();
        } else {
            // Jika user biasa, hanya bisa melihat bawahannya dalam satu perusahaan
            $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')->where('user_id', $user->id)->first();
            if (!$employee) {
                $employees = collect();
            } else {
                $employees = $this->getSubordinates($employee->id)
                    ->where('company_name', $employee->company_name);
            }
        }

        // Ambil daftar department unik dari semua employee
        $departments = Department::pluck('name');

        // Dapatkan employee yang memiliki assessment
        $employeesWithAssessments = $employees->filter(fn($emp) => $emp->assessments()->exists());
        $alcs = Alc::all();

        // Ambil assessment terbaru per employee
        $assessments = Assessment::with(['employee', 'alc'])
            ->whereHas('employee', function ($query) use ($employees) {
                return $query->whereIn('id', $employees->pluck('id'));
            })
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
            ->paginate(5);

        return view('website.assessment.index', compact('assessments', 'employees', 'alcs', 'employeesWithAssessments', 'title', 'departments'));
    }

    // public function history_ajax(Request $request)
    // {
    //     $data = Assessment::select('assessments.id', 'assessments.employee_id', 'assessments.date',
    //                                 'assessments.upload', 'employees.npk as employee_npk', 'employees.name as employee_name',
    //                                 )
    //                         ->join('employees', 'assessments.employee_id', 'employees.id')
    //                         ->with('details')
    //                         ->with('alc')
    //                         ->with('employee')
    //                         ->orderBy('assessments.id', 'ASC');

    //     return DataTables::eloquent($data)->make(true);
    // }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);

        // Hapus juga data terkait di tabel detail_assessments
        DetailAssessment::where('assessment_id', $id)->delete();

        // Hapus file jika ada
        if ($assessment->upload) {
            \Storage::delete('public/' . $assessment->upload);
        }

        // Hapus assessment
        $assessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assessment berhasil dihapus.'
        ]);
    }
    public function show($employee_id)
    {
        $employee = Employee::with('assessments')->find($employee_id);

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        $assessments = Assessment::where('employee_id', $employee_id)
            ->select('id', 'date',  'description','employee_id', 'upload')
            ->orderBy('date', 'desc')
            ->with(['details' => function ($query) {
                $query->select('assessment_id', 'alc_id', 'score', 'strength', 'weakness')
                    ->with(['alc:id,name']);
            }])
            ->get();

        return response()->json([
            'employee' => $employee,
            'assessments' => $assessments
        ]);
    }




    public function showByDate($assessment_id, $date)
    {
        // Ambil assessment berdasarkan ID
        $assessment = Assessment::findOrFail($assessment_id);

        // Ambil employee dari assessment (pastikan kolom employee_id ada di tabel assessments)
        $employee = Employee::with(
            'departments', // ← tambahkan ini
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        )->findOrFail($assessment->employee_id);


        // Ambil data detail_assessment dengan alc (menggunakan Eloquent)
        $assessments = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment_id)
            ->get();

        // Debugging untuk memastikan data tidak null
        if ($assessments->isEmpty()) {
            return back()->with('error', 'Tidak ada data assessment pada tanggal tersebut.');
        }

        // Ambil detail menggunakan join untuk mendapatkan alc_name dan score dari detail_assessment
        $details = DB::table('detail_assessments')
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
            'description' => 'required|string',
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
            'description' => $request->description,
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
        // $token = "v2n49drKeWNoRDN4jgqcdsR8a6bcochcmk6YphL6vLcCpRZdV1";

        // $user = Auth::user();
        // $employee = $user->employee; // ambil employee yang login
        // $rawNumber = $employee->phone_number ?? null;
        // $formattedNumber = preg_replace('/^0/', '62', $rawNumber);

        // if (!$formattedNumber) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Nomor HP Anda tidak tersedia.',
        //     ]);
        // }

        // $message = sprintf(
        // "Hallo Apakah Benar ini Nomor?"
        //     // "✅ Assessment berhasil dikirim!\nID Assessment: %s\nTanggal: %s\nNama Pegawai: %s",
        //     // $assessment->id,
        //     // $assessment->date,
        //     // $assessment->name ?? 'Anda'
        // );

        // $whatsappResponse = Http::asForm()
        //     ->withOptions(['verify' => false])
        //     ->post('https://app.ruangwa.id/api/send_message', [
        //         'token' => $token,
        //         'number' => $formattedNumber,
        //         'message' => $message
        //     ]);



        // Jika mau debug response dari API

        return response()->json([
            'success' => true,
            'message' => 'Data assessment berhasil disimpan.',
            'assessment' => $assessment,
            'assessment_details' => $assessmentDetails,
            // 'whatsapp_response' => $whatsappResponse->body()
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
    public function edit($id)
    {
        $assessment = Assessment::with('details.alc')->findOrFail($id);

        return response()->json([
            'id' => $assessment->id,
            'employee_id' => $assessment->employee_id,
            'date' => $assessment->date,
            'description' => $assessment->description,
            'upload' => $assessment->upload ? asset('storage/' . $assessment->upload) : null, // Buat URL file
            'scores' => $assessment->details->map(fn($d) => [
                'alc_id' => $d->alc_id,
                'score' => $d->score
            ]),
            'strengths' => $assessment->details->whereNotNull('strength')->map(fn($d) => [
                'alc_id' => $d->alc_id,
                'descriptions' => $d->strength
            ])->values(),
            'weaknesses' => $assessment->details->whereNotNull('weakness')->map(fn($d) => [
                'alc_id' => $d->alc_id,
                'descriptions' => $d->weakness
            ])->values(),
            'alc_options' => Alc::select('id', 'name')->get()
        ]);
    }


    public function update(Request $request)
    {

        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
           'description' => 'required|string|max:255',
            'scores' => 'required|array',
            'strength' => 'nullable|array',
            'weakness' => 'nullable|array',
            'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // **Update tabel `assessments`**
        $assessment = Assessment::findOrFail($request->assessment_id);
        $assessment->employee_id = $request->employee_id;
        $assessment->date = $request->date;
        $assessment->description = $request->description;

        // **Handle File Upload**
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $path = $file->store('assessments', 'public');
            $assessment->upload = $path;
        }

        $assessment->save();

        // **Update `detail_assessments` untuk scores, strengths, weaknesses**
        DetailAssessment::where('assessment_id', $assessment->id)->delete();

        foreach ($request->scores as $alc_id => $score) {
            DetailAssessment::create([
                'assessment_id' => $assessment->id,
                'alc_id' => $alc_id,
                'score' => $score,
                'strength' => $request->strength[$alc_id] ?? null,
                'weakness' => $request->weakness[$alc_id] ?? null
            ]);
        }

        return response()->json(['message' => 'Assessment updated successfully']);
    }
}
