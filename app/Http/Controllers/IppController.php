<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IppController
{
    public function create()
    {
        // Ambil identitas dari backend (contoh: dari user login / service lain)
        $user = auth()->user();
        $emp = $user->employee;
        $identitas = [
            'nama'        => $emp->name,
            'department'  => $emp->bagian,
            'division'    => 'MS & IT',
            'section'     => 'Policy Management',
            'date_review' => now()->format('Y-m-d'),
            'pic_review'  => 'Ferry Avianto',
            'on_year'     => now()->format('Y'),
            'no_form'     => 'FRM-HRD-S3-012-00',
        ];
        $title = 'IPP Create';
        return view('website.ipp.create', compact('title', 'identitas'));
    }

    public function store(Request $request)
    {
        dd($request->all());
    }
}
