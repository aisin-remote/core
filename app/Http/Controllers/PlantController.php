<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Plant;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    public function plant($company = null)
    {
        $plants = Plant::with('director')
            ->where('company', $company)
            ->get();

        $directors = Employee::where('position', 'Direktur')
            ->when($company, function ($query) use ($company) {
                $query->where('company_name', $company);
            })->get();

        return view('website.master.plant.index', compact('plants', 'directors', 'company'));
    }



    public function Store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'company' => 'required|string',
            'director_id' => 'required',
        ]);

        $director = Employee::where('id', $request->director_id)
        ->where('position', 'Direktur')
        ->where('company_name', $request->company)
        ->first();

        if (!$director) {
            return redirect()->back()->with('error', 'Direktur tidak sesuai dengan perusahaan yang dipilih.');
        }

        try {
            Plant::create([
                'name' => $request->name,
                'company' => $request->company,
                'director_id' => $request->director_id
            ]);

            return redirect()->back()->with('success', 'Plant berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Plant: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'company' => 'required|string',
            'director_id' => 'required|exists:employees,id',
        ]);

        $director = Employee::where('id', $request->director_id)
        ->where('position', 'Direktur')
        ->where('company_name', $request->company)
        ->first();

        if (!$director) {
            return redirect()->back()->with('error', 'Direktur tidak sesuai dengan perusahaan yang dipilih.');
        }

        try {
            $plant = Plant::findOrFail(id: $id);
            $plant->update([
                'name' => $request->name,
                'company' => $request->company,
                'director_id' => $request->director_id,
            ]);

            return redirect()->back()->with('success', 'Plant berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Plant: ' . $e->getMessage());
        }
    }


    public function plantDestroy($id)
    {
        try {
            $department = Plant::where('id', $id)->firstOrFail();

            $department->delete();
            return redirect()->back()->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus department: ' . $e->getMessage());
        }
    }
}
