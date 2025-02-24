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
        $employees = Employee::all(); // Ambil semua employee
        $alcs = Alc::all(); // Ambil semua alc
        // Ambil data assessment dengan relasi ke employee dan alc
        $assessments = Assessment::with(['employee', 'alc'])
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('npk', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(10); // Pagination


            return view('website.assessment.index', compact('assessments', 'employees', 'alcs'));

    }
    public function create()
    {
        $employees = Employee::all(); // Ambil semua employee
        $alcs = Alc::all(); // Ambil semua alc_id

        return view('assessments.create', compact('employees', 'alcs'));
    }

    /**
     * Menyimpan data assessment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,npk',
            'date' => 'required|date',
            'upload' => 'nullable|string|max:200',
            'alc_id' => 'required|array', // Pastikan alc_id berupa array
            'alc_id.*' => 'exists:alc,id', // Pastikan setiap alc_id ada di tabel alc
            'score' => 'required|array',
            'score.*' => 'string|max:2',

        ]);

        foreach ($request->alc_id as $key => $alc_id) {
            Assessment::create([
                'employee_id' => $request->employee_id,
                'alc_id' => $alc_id,
                'score' => $request->score[$key],
                'description' => $request->description[$key],
                'date' => $request->date,
                'upload' => $request->upload,
            ]);
        }

        return redirect()->route('assessments.index')->with('success', 'Data berhasil disimpan.');
    }
}
