<?php

namespace App\Http\Controllers;

use App\Models\GroupCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupCompetencyController extends Controller
{
    // Menampilkan daftar Group Competency
    public function index()
    {
        $title = 'Group Competency';
        $group_competency = GroupCompetency::paginate(10);
        return view('website.group_competency.index', compact('group_competency', 'title'));
    }

    // Menampilkan form untuk membuat data baru
    public function create()
    {
        $title = 'Add Group Competency';
        return view('website.group_competency.create', compact('title'));
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]); 

        // Simpan Group Competency ke database
        GroupCompetency::create($validatedData);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('group_competency.index')
                         ->with('success', 'Data Group Competency berhasil disimpan!');
    }

    // Menampilkan form edit untuk data yang sudah ada
    public function edit($id)
    {
        $title = 'Update Group Competency';
        $group = GroupCompetency::findOrFail($id);
        return view('website.group_competency.update', compact('group', 'title'));
    } 

    // Memperbarui data di database
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string'
        ]);

        $group = GroupCompetency::findOrFail($id);
        $group->update($validatedData);

        return redirect()->route('group_competency.index')
                         ->with('success', 'Group Competency updated successfully!');
    }

    // Menghapus data dari database
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $group = GroupCompetency::findOrFail($id);
            $group->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Group Competency deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete Group Competency!');
        }
    }
}
