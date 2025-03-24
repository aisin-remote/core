<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmpCompetencyController extends Controller
{
    // Data dummy sebagai simulasi "emp_competency"
    protected $dummyEmpCompetencies = [
        [
            'competency_id' => 'Managerial Skill',
            'employee_id'   => 'Aceng',
            'act'           => 3,
            'plan'          => 3,
            'progress'      => 'Selesai',
            'weight'        => 2,
            'created_at'    => '2025-03-24 00:00:00',
            'updated_at'    => '2025-03-24 00:00:00',
        ],
        [
            'competency_id' => 'English',
            'employee_id'   => 'Tatang',
            'act'           => 4,
            'plan'          => 4,
            'progress'      => 'Selesai',
            'weight'        => 2,
            'created_at'    => '2025-03-24 00:00:00',
            'updated_at'    => '2025-03-24 00:00:00',
        ],
        // Tambahkan data dummy lain jika diperlukan...
    ];

    // Tampilkan list emp_competency
    public function index()
    {
        $title = 'Emp Competency';
        $empCompetencies = $this->dummyEmpCompetencies;
        return view('website.emp_competency.index', compact('empCompetencies', 'title'));
    }

    // Tampilkan form untuk menambah data baru
    public function create()
    {
        $title = 'Create Emp Competency';
        return view('website.emp_competency.create', compact('title'));
    }

    // Simulasi penyimpanan data baru (tidak tersimpan secara nyata)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'competency_id' => 'required|integer',
            'employee_id'   => 'required|integer',
            'act'           => 'required|integer',
            'plan'          => 'required|integer',
            'progress'      => 'required|integer',
            'weight'        => 'required|integer',
        ]);

        // Simulasi penyimpanan data
        return redirect()->route('emp_competency.index')
                         ->with('success', 'Emp Competency berhasil ditambahkan (simulasi).');
    }

    // Tampilkan form edit untuk data yang sudah ada
    public function edit($competency_id, $employee_id)
    {
        $title = 'Edit Emp Competency';
        $empCompetency = collect($this->dummyEmpCompetencies)->firstWhere(function($item) use ($competency_id, $employee_id) {
            return $item['competency_id'] == $competency_id && $item['employee_id'] == $employee_id;
        });

        if (!$empCompetency) {
            return redirect()->route('emp_competency.index')
                             ->with('error', 'Emp Competency tidak ditemukan.');
        }
        return view('website.emp_competency.edit', compact('empCompetency', 'title'));
    }

    // Simulasi update data
    public function update(Request $request, $competency_id, $employee_id)
    {
        $validated = $request->validate([
            'competency_id' => 'required|integer',
            'employee_id'   => 'required|integer',
            'act'           => 'required|integer',
            'plan'          => 'required|integer',
            'progress'      => 'required|integer',
            'weight'        => 'required|integer',
        ]);

        return redirect()->route('emp_competency.index')
                         ->with('success', 'Emp Competency berhasil diupdate (simulasi).');
    }

    // Simulasi hapus data
    public function destroy($competency_id, $employee_id)
    {
        return redirect()->route('emp_competency.index')
                         ->with('success', 'Emp Competency berhasil dihapus (simulasi).');
    }
}
