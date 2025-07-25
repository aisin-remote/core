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
            ->when($company, function ($query) use ($company) {
                $query->whereHas('director', function ($q) use ($company) {
                    $q->where('company_name', $company);
                });
            })->get();

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
            'director_id' => 'required'
        ]);

        try {
            Plant::create([
                'name' => $request->name,
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
            'name' => 'required|string' . $id,
            'director_id' => 'required|exists:employees,id',
        ]);

        try {
            $plant = Plant::findOrFail($id);
            $plant->update([
                'name' => $request->name,
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
