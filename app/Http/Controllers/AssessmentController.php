<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Employee;
use App\Models\Alc;

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


    // Ambil assessment dan group berdasarkan `date`
    $assessments = Assessment::where('employee_id', $employee_id)
    ->selectRaw('date, MAX(id) as id, employee_id') // Tambahkan employee_id
    ->groupBy('date', 'employee_id') // Pastikan group by juga dengan employee_id
    ->orderBy('date', 'desc')
    ->get();


    return view('website.assessment.show', compact('employee', 'assessments'));

}
public function showByDate($employee_id, $date)
{
    $employee = Employee::where('npk', $employee_id)->firstOrFail();

    $assessments = Assessment::where('employee_id', $employee->npk)
    ->where('date', $date)
    ->get();


    return view('website.assessment.detail', compact('employee', 'assessments', 'date'));
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
        'employee_id' => 'required|exists:employees,npk',
        'date' => 'required|date',
        'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        'alc_ids' => 'required|array',
        'alc_ids.*' => 'exists:alc,id',
        'scores' => 'nullable|array',
        'scores.*' => 'nullable|string|max:2',
        'descriptions' => 'nullable|array',
    ]);

    $mergedData = [];
    foreach ($request->alc_ids as $index => $alc_id) {
        if (!isset($mergedData[$alc_id])) {
            $mergedData[$alc_id] = [
                'score' => isset($request->scores[$alc_id]) ? $request->scores[$alc_id] : "0",
                'description' => $request->descriptions[$alc_id] ?? "",
            ];
        } else {
            // Hindari duplikasi deskripsi
            $existingDescriptions = explode(" ", $mergedData[$alc_id]['description']);
            $newDescription = $request->descriptions[$alc_id] ?? "";

            if (!in_array($newDescription, $existingDescriptions)) {
                $mergedData[$alc_id]['description'] .= " " . $newDescription;
            }
        }
    }





    $assessments = [];
    foreach ($mergedData as $alc_id => $data) {
        $assessments[] = Assessment::create([
            'employee_id' => $request->employee_id,
            'alc_id' => $alc_id,
            'score' => $data['score'],
            'description' => trim($data['description']), // Hilangkan spasi berlebih
            'date' => $request->date,
            'upload' => $request->upload,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data assessment berhasil disimpan.',
        'assessments' => $assessments,
    ]);
}



}
