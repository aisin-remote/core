<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssessmentController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $title = 'Employee';
    $employee = Employee::all(); // Ambil semua data karyawan
    return view('website.employee.index', compact('employee', 'title'));
});

Route::get('/login', function(){
    return view('website.auth.login');
});
Route::get('/register', function(){
    return view('website.auth.register');
});

Route::prefix('hav')->group(function () {
    Route::get('/', function (){
        return view('website.hav.index');
    });
});
Route::prefix('employee')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create'); // Menampilkan form create
    Route::post('/', [EmployeeController::class, 'store'])->name('employee.store'); // Menyimpan data
    Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit'); // Menampilkan form edit
    Route::put('/{id}', [EmployeeController::class, 'update'])->name('employee.update'); // Memperbarui data
    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy'); // Menghapus data
    Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
});




Route::prefix('assessment')->group(function () {
    Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::post('/', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::put('/{id}', [AssessmentController::class, 'update'])->name('assessments.update'); // Route update
    Route::delete('/{id}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
});

Route::prefix('rtc')->group(function () {
    Route::get('/', function (){
        return view('website.rtc.index');
    });
});
Route::prefix('idp')->group(function () {
    Route::get('/', function (){
        $employee = Employee::all(); // Ambil semua data karyawan
        return view('website.idp.index', [
            'employees' => $employee
        ]);
    });
});
