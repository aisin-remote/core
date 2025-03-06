<?php

use App\Models\Employee;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RegisterController;
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

Route::middleware('guest')->group(function(){
    Route::prefix('register')->group(function () {
        Route::get('/', [RegisterController::class, 'index'])->name('register.index');
        Route::post('/store', [RegisterController::class, 'store'])->name('register.store');
    });

    Route::prefix('login')->group(function () {
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('login.authenticate');
    });
});

Route::middleware('auth')->group(function(){
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
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('employee.show'); // Menampilkan detail Employee


        Route::prefix('profile')->group(function(){
            Route::get('/', [EmployeeController::class, 'profile'])->name('employee.profile');
        });
    });


    Route::prefix('assessment')->group(function () {
        Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/{employee_id}/{date}', [AssessmentController::class, 'showByDate'])->name('assessments.showByDate');

        Route::get('/{employee_id}', [AssessmentController::class, 'show'])->name('assessments.show'); // Ditaruh di bawah agar tidak bentrok
        Route::post('/', [AssessmentController::class, 'store'])->name('assessments.store');
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

    Route::prefix('master')->group(function () {
        Route::get('/employee', [MasterController::class, 'employee'])->name('employee.master.index');
        Route::get('/assesment', [MasterController::class, 'assesment'])->name('assesment.master.index');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');
});
