<?php

use App\Models\Employee;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HavController;
use App\Http\Controllers\IdpController;
use App\Http\Controllers\RtcController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\EmpCompetencyController;
use App\Http\Controllers\GroupCompetencyController;

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


// Route::resource('emp_competency', EmpCompetencyController::class);
Route::get('/create', [EmpCompetencyController::class, 'create'])->name('emp_competency.create');

Route::resource('group_competency', GroupCompetencyController::class);

Route::get('/competencies', [CompetencyController::class, 'index'])->name('competencies.index');
Route::get('/competencies/create', [CompetencyController::class, 'create'])->name('competencies.create');
Route::post('/competencies/store', [CompetencyController::class, 'store'])->name('competencies.store');
Route::get('/competencies/{competency}/edit', [CompetencyController::class, 'edit'])->name('competencies.edit');
Route::put('/competencies/{competency}', [CompetencyController::class, 'update'])->name('competencies.update');
Route::delete('/competencies/{competency}', [CompetencyController::class, 'destroy'])->name('competencies.destroy');

Route::middleware('guest')->group(function () {
    Route::prefix('register')->group(function () {
        Route::get('/', [RegisterController::class, 'index'])->name('register.index');
        Route::post('/store', [RegisterController::class, 'store'])->name('register.store');
    });

    Route::prefix('login')->group(function () {
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('login.authenticate');
    });

    Route::get('/', function () {
        return view('website.auth.login');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('website.dashboard.index');
    })->name('dashboard.index');

    Route::get('/master_schedule', function () {
        return view('website.dashboard.master');
    })->name('master_schedule.index');

    Route::get('/people', function () {
        return view('website.dashboard.people');
    })->name('people.index');

    Route::prefix('hav')->group(function () {
        Route::get('/list-create', [HavController::class, 'listCreate'])->name('hav.list-create');
        Route::get('/generate-create/{id}', [HavController::class, 'generateCreate'])->name('hav.generate-create');
        Route::get('/update/{id}', [HavController::class, 'update'])->name('hav.update');
        Route::post('/update-rating', [HavController::class, 'updateRating'])->name('update.rating');
        Route::get('/hav/ajax-list', [HavController::class, 'ajaxList'])->name('hav.ajax.list');
        Route::delete('/{id}', [HavController::class, 'destroy'])->name('hav.destroy');
        Route::get('/hav/export', [HavController::class, 'export'])->name('hav.export');
        Route::get('/history/{id}', [HavController::class, 'show'])->name('hav.show');
        Route::get('/get3-last-performance/{id}/{year}', [HavController::class, 'get3LastPerformance'])->name('hav.get3LastPerformance');
        Route::post('/import', [HavController::class, 'import'])->name('hav.import');
        Route::patch('/approve/{id}', [HavController::class, 'approve'])->name('hav.approve');
        Route::patch('/reject/{id}', [HavController::class, 'reject'])->name('hav.reject');
        Route::get('/get-history/{hav_id}', [HavController::class, 'getComment'])->name('hav.getComment');

        Route::get('/hav/test-import', function () {});

        // Pindahkan ke atas
        Route::get('/list/{company?}', [HavController::class, 'list'])->name('hav.list');
        Route::get('/{company?}', [HavController::class, 'index'])->name('hav.index');
    });
    Route::prefix('approval')->group(function () {
        Route::get('/hav', [HavController::class, 'approval'])->name('hav.approval');
        Route::get('/idp', [IdpController::class, 'approval'])->name('idp.approval');
        Route::get('/idp/{id}', [IdpController::class, 'approve'])->name('idp.approve');
        Route::post('idp/revise', [IdpController::class, 'revise'])->name('idp.revise');
    });

    Route::prefix('employee')->group(function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create'); // Menampilkan form create
        Route::get('/{company?}', [EmployeeController::class, 'index'])->name('employee.index');
        Route::post('/', [EmployeeController::class, 'store'])->name('employee.store'); // Menyimpan data
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit'); // Menampilkan form edit
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('employee.update'); // Memperbarui data
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy'); // Menghapus data
        Route::get('/detail/{id}', [EmployeeController::class, 'show'])->name('employee.show'); // Menampilkan detail Employee

        Route::post('/master/import', [EmployeeController::class, 'import'])->name('employee.import');
        Route::post('/status/{id}', [EmployeeController::class, 'status'])->name('employee.status');

        // promotion
        Route::prefix('promotion')->group(function () {
            Route::delete('/delete/{id}', [EmployeeController::class, 'promotionDestroy'])->name('promotion.destroy');
        });

        // work experience
        Route::prefix('work-experience')->group(function () {
            Route::post('/store', [EmployeeController::class, 'workExperienceStore'])->name('work-experience.store');
            Route::put('/update/{id}', [EmployeeController::class, 'workExperienceUpdate'])->name('work-experience.update');
            Route::delete('/delete/{id}', [EmployeeController::class, 'workExperienceDestroy'])->name('work-experience.destroy');
        });

        // education
        Route::prefix('education')->group(function () {
            Route::post('/store', [EmployeeController::class, 'educationStore'])->name('education.store');
            Route::put('/update/{id}', [EmployeeController::class, 'educationUpdate'])->name('education.update');
            Route::delete('/delete/{id}', [EmployeeController::class, 'educationDestroy'])->name('education.destroy');
        });

        // appraisal
        Route::prefix('appraisal')->group(function () {
            Route::post('/store', [EmployeeController::class, 'appraisalStore'])->name('appraisal.store');
            Route::put('/update/{id}', [EmployeeController::class, 'appraisalUpdate'])->name('appraisal.update');
            Route::delete('/delete/{id}', [EmployeeController::class, 'appraisalDestroy'])->name('appraisal.destroy');
        });

        // astra_training
        Route::prefix('astra_training')->group(function () {
            Route::post('/store', [EmployeeController::class, 'astraTrainingStore'])->name('astra_training.store');
            Route::put('/update/{id}', [EmployeeController::class, 'astraTrainingUpdate'])->name('astra_training.update');
            Route::delete('/delete/{id}', [EmployeeController::class, 'astraTrainingDestroy'])->name('astra_training.destroy');
        });

        // external_training
        Route::prefix('external_training')->group(function () {
            Route::post('/store', [EmployeeController::class, 'externalTrainingStore'])->name('external_training.store');
            Route::put('/update/{id}', [EmployeeController::class, 'externalTrainingUpdate'])->name('external_training.update');
            Route::delete('/delete/{id}', [EmployeeController::class, 'externalTrainingDestroy'])->name('external_training.destroy');
        });

        Route::prefix('profile')->group(function () {
            Route::get('/{id}/profile', [EmployeeController::class, 'profile'])->name('employee.profile');
        });
    });


    Route::prefix('assessment')->group(function () {
        Route::get('/{company?}', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::post('/update', [AssessmentController::class, 'update'])->name('assessments.update');

        Route::get('/detail/{id}', [AssessmentController::class, 'getAssessmentDetail']);

        Route::get('/history/{employee_id}', [AssessmentController::class, 'show'])->name('assessments.show'); // Ubah ke 'history/{employee_id}'

        Route::get('/{assessment_id}/{date}', [AssessmentController::class, 'showByDate'])->name('assessments.showByDate'); // Pindahkan ke bawah


        Route::post('/', [AssessmentController::class, 'store'])->name('assessments.store');
        Route::delete('/{id}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
    });

    Route::prefix('rtc')->group(function () {
        Route::get('/summary', [RtcController::class, 'summary'])->name('rtc.summary');
        Route::get('/detail', [RtcController::class, 'detail'])->name('rtc.detail');
        Route::get('/list', [RtcController::class, 'list'])->name('rtc.list');
        Route::get('/update', [RtcController::class, 'update'])->name('rtc.update');
        Route::get('/{company?}', [RtcController::class, 'index'])->name('rtc.index');
    });

    Route::prefix('idp')->group(function () {
        Route::get('/{company?}', [IdpController::class, 'index'])->name('idp.index');
        Route::post('/idp/store', [IdpController::class, 'store'])->name('idp.store');
        Route::post('/idp/store-mid-year/{employee_id}', [IdpController::class, 'storeOneYear'])->name('idp.storeOneYear');
        Route::post('/send-idp', [IdpController::class, 'sendIdpToSupervisor'])->name('send.idp');
        Route::post('/idp/store-one-year/{employee_id}', [IdpController::class, 'storeMidYear'])->name('idp.storeMidYear');
        Route::get('/development-data/{employee_id}', [IdpController::class, 'showDevelopmentData'])->name('development.data');
        Route::get('/development-mid-data/{employee_id}', [IdpController::class, 'showDevelopmentMidData'])->name('development.mid.data');
        Route::delete('/idp/delete/{id}', [IdpController::class, 'destroy'])->name('idp.destroy');
        Route::get('/getData', [IdpController::class, 'getData'])->name('idp.getData');
        Route::get('/export-template/{employee_id}', [IdpController::class, 'exportTemplate'])->name('idp.exportTemplate');
        Route::get('/', function () {
            $employee = Employee::all(); // Ambil semua data karyawan
            return view('website.idp.index', [
                'employees' => $employee
            ]);
        });
    });

    Route::get('/idp/export-template/{employee_id}', [IdpController::class, 'exportTemplate'])
        ->name('idp.exportTemplate');

    Route::prefix('master')->group(function () {
        Route::get('/filter', [MasterController::class, 'filter'])->name('filter.master');
        Route::get('/employee/{company?}', [MasterController::class, 'employee'])->name('employee.master.index');
        Route::get('/assesment', [MasterController::class, 'assesment'])->name('assesment.master.index');

        Route::prefix('plant')->group(function () {
            Route::get('/', [PlantController::class, 'plant'])->name('plant.master.index');
            Route::post('/store', [PlantController::class, 'Store'])->name('plant.master.store');
            Route::delete('/delete/{id}', [PlantController::class, 'plantDestroy'])->name('plant.master.destroy');
        });

        Route::prefix('department')->group(function () {
            Route::get('/', [MasterController::class, 'department'])->name('department.master.index');
            Route::post('/store', [MasterController::class, 'departmentStore'])->name('department.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'departmentDestroy'])->name('department.master.destroy');
        });

        Route::prefix('division')->group(function () {
            Route::get('/', [MasterController::class, 'division'])->name('division.master.index');
            Route::post('/store', [MasterController::class, 'divisionStore'])->name('division.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'divisionDestroy'])->name('division.master.destroy');
        });


        Route::prefix('section')->group(function () {
            Route::get('/', [MasterController::class, 'section'])->name('section.master.index');
            Route::post('/store', [MasterController::class, 'sectionStore'])->name('section.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'sectionDestroy'])->name('section.master.destroy');
        });

        Route::prefix('subSection')->group(function () {
            Route::get('/', [MasterController::class, 'subSection'])->name('subSection.master.index');
            Route::post('/store', [MasterController::class, 'subSectionStore'])->name('subSection.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'subSectionDestroy'])->name('subSection.master.destroy');
        });

        Route::prefix('grade')->group(function () {
            Route::get('/', [MasterController::class, 'grade'])->name('grade.master.index');
            Route::post('/store', [MasterController::class, 'gradeStore'])->name('grade.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'gradeDestroy'])->name('grade.master.destroy');
        });
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');
});
