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
