<?php

namespace App\Http\Controllers;

use App\Models\GroupCompetency;
use Illuminate\Http\Request;

class GroupCompetencyController extends Controller
{
    // Menampilkan daftar Group Competency
    public function index()
    {
        $title = 'Group Competency';
        $group = GroupCompetency::paginate(10);
        return view('website.group_competency.index', compact('group', 'title'));
    }

    // Menampilkan form untuk membuat data baru
    public function create()
    {
        $title = 'Tambah Group Competency';
        return view('website.group_competency.create', compact('title'));
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        GroupCompetency::create($validated);
        return redirect()->route('group_competency.index')
                         ->with('success', 'Group Competency berhasil ditambahkan.');
    }

    // Menampilkan form edit untuk data yang sudah ada
    public function edit(GroupCompetency $group_competency)
    {
        $title = 'Edit Group Competency';
        return view('website.group_competency.edit', compact('group_competency', 'title'));
    }    

    // Memperbarui data di database
    public function update(Request $request, GroupCompetency $group_competency)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group_competency->update($validated);
        return redirect()->route('group_competency.index')
                         ->with('success', 'Group Competency berhasil diupdate.');
    }

    // Menghapus data dari database
    public function destroy(GroupCompetency $group_competency)
    {
        $group_competency->delete();
        return redirect()->route('group_competency.index')
                         ->with('success', 'Group Competency berhasil dihapus.');
    }
}
