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
        
    }

    // Menyimpan data baru ke database
    public function store()
    {
       
    }

    // Menampilkan form edit untuk data yang sudah ada
    public function edit()
    {
        
    }    

    // Memperbarui data di database
    public function update()
    {

    }

    // Menghapus data dari database
    public function destroy()
    {

    }
}
