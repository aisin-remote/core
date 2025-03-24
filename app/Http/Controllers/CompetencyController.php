<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use Illuminate\Http\Request;

class CompetencyController extends Controller
{
    // Menampilkan daftar Competency
    public function index()
    {
        $title = 'Competency';
        // Ambil data competency dari database dengan paginate
        $competencies = Competency::paginate(10);
        return view('website.competency.index', compact('competencies', 'title'));
    }

    // Menampilkan form untuk membuat data baru
    public function create()
    {
        $title = 'Create Competency';
        return view('website.competency.create', compact('title'));
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'description'      => 'nullable|string',
            'group_competency_id' => 'required|integer',
            'dept_id'       => 'required|integer',
            'role_id'             => 'required|integer',
        ]);

        Competency::create($validated);
        return redirect()->route('competency.index')
                         ->with('success', 'Competency berhasil ditambahkan.');
    }

    // Menampilkan form edit untuk data yang sudah ada
    public function edit(Competency $competency)
    {
        $title = 'Edit Competency';
        return view('website.competency.edit', compact('competency', 'title'));
    }

    // Memperbarui data di database
    public function update(Request $request, Competency $competency)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'description'      => 'nullable|string',
            'group_competency_id' => 'required|integer',
            'dept_id'       => 'required|integer',
            'role_id'             => 'required|integer',
        ]);

        $competency->update($validated);
        return redirect()->route('competency.index')
                         ->with('success', 'Competency berhasil diupdate.');
    }

    // Menghapus data dari database
    public function destroy(Competency $competency)
    {
        $competency->delete();
        return redirect()->route('competency.index')
                         ->with('success', 'Competency berhasil dihapus.');
    }
}
