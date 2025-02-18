<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Tampilkan daftar assessment.
     */
    public function index()
    {
        $title = 'Assessment';
        $assessments = DB::table('assessments')
        ->leftJoin('employees', 'assessments.employee_npk', '=', 'employees.npk')
        ->select('assessments.*', 'employees.name', 'employees.position', 'employees.npk', 'employees.birthday_date')
        ->get();

        $employees = Employee::all(); // Pastikan ini menggunakan 'employees', bukan 'employee'

        return view('website.assessment.index', compact('assessments', 'employees', 'title')); // Gunakan 'employees' bukan 'employee'
    }


    /**
     * Simpan data assessment baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_npk' => 'required|exists:employees,npk',
            'date' => 'required|date',
            'vision_business_sense' => 'required|string|max:2',
            'customer_focus' => 'required|string|max:2',
            'interpersonal_skil' => 'required|string|max:2',
            'analysis_judgment' => 'required|string|max:2',
            'planning_driving_action' => 'required|string|max:2',
            'leading_motivating' => 'required|string|max:2',
            'teamwork' => 'required|string|max:2',
            'drive_courage' => 'required|string|max:2',
            'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $uploadPath = null;
        if ($request->hasFile('upload')) {
            $uploadPath = $request->file('upload')->store('uploads/assessments', 'public');
        }

        Assessment::create([
            'employee_npk' => $request->employee_npk,
            'date' => $request->date,
            'vision_business_sense' => $request->vision_business_sense, // ubah '&' menjadi '_'
            'customer_focus' => $request->customer_focus,
            'interpersonal_skil' => $request->interpersonal_skil,
            'analysis_judgment' => $request->analysis_judgment, // ubah '&' menjadi '_'
            'planning_driving_action' => $request->planning_driving_action, // ubah '&' menjadi '_'
            'leading_motivating' => $request->leading_motivating, // ubah '&' menjadi '_'
            'teamwork' => $request->teamwork,
            'drive_courage' => $request->drive_courage, // ubah '&' menjadi '_'
            'upload' => $uploadPath,
        ]);



        return response()->json(['message' => 'Assessment berhasil ditambahkan!'], 200);
    }
    public function edit($id)
    {
        $assessment = Assessment::where('id', $id)->firstOrFail(); // Cari berdasarkan npk
        return view('website.assessment.update', compact('assessment'));
    }
    /**
 * Perbarui data assessment yang sudah ada.
 */
public function update(Request $request, $id)
{
    $request->validate([
        'employee_npk' => 'required|exists:employees,npk',
        'date' => 'required|date',
        'vision_business_sense' => 'required|string|max:2',
        'customer_focus' => 'required|string|max:2',
        'interpersonal_skil' => 'required|string|max:2',
        'analysis_judgment' => 'required|string|max:2',
        'planning_driving_action' => 'required|string|max:2',
        'leading_motivating' => 'required|string|max:2',
        'teamwork' => 'required|string|max:2',
        'drive_courage' => 'required|string|max:2',
        'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
    ]);

    $assessment = Assessment::findOrFail($id);

    // Proses file upload jika ada perubahan file
    if ($request->hasFile('upload')) {
        // Hapus file lama jika ada
        if ($assessment->upload) {
            \Storage::disk('public')->delete($assessment->upload);
        }

        // Simpan file baru
        $uploadPath = $request->file('upload')->store('uploads/assessments', 'public');
        $assessment->upload = $uploadPath;
    }

    // Update data assessment
    $assessment->update([
        'employee_npk' => $request->employee_npk,
        'date' => $request->date,
        'vision_business_sense' => $request->vision_business_sense,
        'customer_focus' => $request->customer_focus,
        'interpersonal_skil' => $request->interpersonal_skil,
        'analysis_judgment' => $request->analysis_judgment,
        'planning_driving_action' => $request->planning_driving_action,
        'leading_motivating' => $request->leading_motivating,
        'teamwork' => $request->teamwork,
        'drive_courage' => $request->drive_courage,
    ]);

    return response()->json(['message' => 'Assessment berhasil diperbarui!'], 200);
}



    /**
     * Hapus data assessment.
     */
    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->delete();

        return response()->json(['message' => 'Assessment berhasil dihapus!'], 200);
    }
}
