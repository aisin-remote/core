<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Employee;

class IdpController extends Controller
{

    public function exportTemplate($employee_id)
{
    $filePath = 'public/templates/idp_template.xlsx'; 


    if (!Storage::exists($filePath)) {
        return back()->with('error', 'File template tidak ditemukan.');
    }

    $employee = Employee::find($employee_id);
    if (!$employee) {
        return back()->with('error', 'Employee tidak ditemukan.');
    }

    $fileName = 'IDP_' . str_replace(' ', '_', $employee->name) . '.xlsx';

    return Storage::download($filePath, $fileName);
}

}
