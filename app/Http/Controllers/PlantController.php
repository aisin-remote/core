<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    public function plant()
    {
        $plants = Plant::all();
        return view('website.master.plant.index', compact('plants'));
    }
    public function Store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:plants,name',
        ]);

        try {
            Plant::create(['name' => $request->name]);

            return redirect()->back()->with('success', 'Plant berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Plant: ' . $e->getMessage());
        }
    }

    public function plantDestroy($id)
    {
        try {
            $plant = Plant::findOrFail($id);
            $plant->delete();

            return redirect()->back()->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus department: ' . $e->getMessage());
        }
    }
}
