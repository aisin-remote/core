<?php

namespace App\Http\Controllers;

use App\Models\EmpCompetency;
use Illuminate\Http\Request;

class EmpCompetencyController extends Controller
{
    // Tampilkan list emp_competency
    public function index()
    {
        $title = 'Emp Competency';
        $empCompetencies = EmpCompetency::paginate(10);
        return view('website.emp_competency.index', compact('empCompetencies', 'title'));
    }

    // Tampilkan form untuk menambah data baru
    public function create()
    {
    
    }

    // Simulasi penyimpanan data baru (tidak tersimpan secara nyata)
    public function store(Request $request)
    {
    
    }

    // Tampilkan form edit untuk data yang sudah ada
    public function edit()
    {
        
    }

    // Simulasi update data
    public function update()
    {
      
    }

    // Simulasi hapus data
    public function destroy()
    {
        
    }
}
