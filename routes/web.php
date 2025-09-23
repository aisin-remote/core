<?php

use App\Models\Employee;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HavController;
use App\Http\Controllers\IcpController;
use App\Http\Controllers\IdpController;
use App\Http\Controllers\RtcController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ToDoListController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\GroupCompetencyController;
use App\Http\Controllers\EmployeeCompetencyController;
use App\Http\Controllers\ChecksheetAssessmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IppController;
use Illuminate\Support\Facades\Request;

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


/* Employee Competency */

Route::prefix('employeeCompetencies')->group(function () {
    Route::resource('/', EmployeeCompetencyController::class);
    Route::get('/{company?}', [EmployeeCompetencyController::class, 'index'])->name('employeeCompetencies.index');
    Route::get('/create', [EmployeeCompetencyController::class, 'create'])->name('employeeCompetencies.create');
    Route::put('/{employeeCompetency}', [EmployeeCompetencyController::class, 'update'])->name('employeeCompetencies.update');
    Route::delete('/{employeeCompetency}', [EmployeeCompetencyController::class, 'destroy'])->name('employeeCompetencies.destroy');
    Route::post('/store', [EmployeeCompetencyController::class, 'store'])->name('employeeCompetencies.store');
    Route::get('/get-employees', [EmployeeCompetencyController::class, 'getEmployees']);
    Route::get('/get-competencies', [EmployeeCompetencyController::class, 'getCompetencies']);
    Route::get('/employee/{employee}', [EmployeeCompetencyController::class, 'show'])->name('employeeCompetencies.show');
    Route::delete('/delete-all/{employee}', [EmployeeCompetencyController::class, 'destroyAll'])->name('employeeCompetencies.destroyAll');
    Route::get('/{id}/checksheet', [EmployeeCompetencyController::class, 'checksheet']);
});

/* Group Competency */
Route::resource('group_competency', GroupCompetencyController::class);
Route::resource('competency', CompetencyController::class);

/* Competency */
Route::get('/competencies', [CompetencyController::class, 'index'])->name('competencies.index');
Route::get('/competencies/create', [CompetencyController::class, 'create'])->name('competencies.create');
Route::post('/competencies/store', [CompetencyController::class, 'store'])->name('competencies.store');
Route::get('/competencies/{competency}/edit', [CompetencyController::class, 'edit'])->name('competencies.edit');
Route::put('/competencies/{competency}', [CompetencyController::class, 'update'])->name('competencies.update');
Route::delete('/competencies/{competency}', [CompetencyController::class, 'destroy'])->name('competencies.destroy');

/* Checksheet */
Route::get('/checksheet', [ChecksheetController::class, 'index'])->name('checksheet.index');
Route::get('/checksheet/create', [ChecksheetController::class, 'create'])->name('checksheet.create');
Route::post('/checksheet/store', [ChecksheetController::class, 'store'])->name('checksheet.store');
Route::delete('/checksheet/{checksheet}', [ChecksheetController::class, 'destroy'])->name('checksheet.destroy');

/* Checksheet Assessment */
Route::get('/checksheet-assessment/{competency}', [ChecksheetAssessmentController::class, 'index'])
    ->name('checksheet-assessment.index');
Route::post('/checksheet-assessment', [ChecksheetAssessmentController::class, 'store'])
    ->name('checksheet-assessment.store');


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

Route::middleware(['auth', 'force.password.change'])->group(function () {
    Route::middleware(['company.scope', 'redirect.if.cannot.dashboard'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
        Route::get('/dashboard/list', [DashboardController::class, 'list'])->name('dashboard.list');
    });

    Route::get('/schedule', function () {
        return view('website.dashboard.schedule.index');
    })->name('schedule.index');

    Route::get('/master_schedule', function () {
        return view('website.dashboard.schedule.master');
    })->name('master_schedule.index');

    Route::get('/people', function () {
        return view('website.dashboard.schedule.people');
    })->name('people.index');

    Route::prefix('todolist')->group(function () {
        Route::get('/', [ToDoListController::class, 'index'])->name('todolist.index');
    });

    Route::prefix('icp')->group(function () {
        // Pindahkan create di atas agar tidak tertabrak oleh {company}

        Route::get('/', [IcpController::class, 'assign'])->name('icp.assign');

        Route::get('/create/{employee_id}', [IcpController::class, 'create'])->name('icp.create');
        Route::post('/', [IcpController::class, 'store'])->name('icp.store');
        Route::get('/list/{company?}', [IcpController::class, 'index'])->name('icp.list');

        Route::get('/history/{id}', [IcpController::class, 'show'])->name('icp.show');
        Route::get('/export/{employee_id}', [IcpController::class, 'export'])->name('icp.export');

        Route::get('/edit/{id}', [IcpController::class, 'edit'])->name('icp.edit');
        Route::put('/{id}', [IcpController::class, 'update'])->name('icp.update');

        Route::post('/delete/{id}', [IcpController::class, 'destroy'])->name('icp.destroy');
        Route::post('/{id}/submit', [IcpController::class, 'submit'])->name('icp.submit');
    });

    Route::prefix('hav')->group(function () {
        Route::get('/list-create', [HavController::class, 'listCreate'])->name('hav.list-create');
        Route::get('/generate-create/{id}', [HavController::class, 'generateCreate'])->name('hav.generate-create');
        Route::get('/update/{id}', [HavController::class, 'update'])->name('hav.update');
        Route::post('/update-rating', [HavController::class, 'updateRating'])->name('update.rating');
        Route::get('/hav/ajax-list', [HavController::class, 'ajaxList'])->name('hav.ajax.list');
        Route::delete('/{id}', [HavController::class, 'destroy'])->name('hav.destroy');
        Route::get('/hav/export', [HavController::class, 'export'])->name('hav.export');
        Route::get('/download-upload/{havId}', [HavController::class, 'downloadLatestUpload'])
            ->name('download.upload');
        Route::get('/history/{id}', [HavController::class, 'show'])->name('hav.show');
        Route::get('/get3-last-performance/{id}/{year}', [HavController::class, 'get3LastPerformance'])->name('hav.get3LastPerformance');
        Route::post('/import', [HavController::class, 'import'])->name('hav.import');
        Route::get('/get-history/{hav_id}', [HavController::class, 'getComment'])->name('hav.getComment');

        Route::get('/exportassign/{id}', [HavController::class, 'exportassign'])->name('hav.exportassign');

        // Pindahkan ke atas
        Route::get('/list/{company?}', [HavController::class, 'list'])->name('hav.list');
        Route::get('/assign/{company?}', [HavController::class, 'assign'])->name('hav.assign');
        Route::get('/{company?}', [HavController::class, 'index'])->name('hav.index');
    });

    Route::prefix('approval')->group(function () {
        Route::get('/hav', [HavController::class, 'approval'])->name('hav.approval');
        Route::patch('/hav/approve/{id}', [HavController::class, 'approve'])->name('hav.approve');
        Route::patch('/hav/reject/{id}', [HavController::class, 'reject'])->name('hav.reject');

        Route::get('/idp', [IdpController::class, 'approval'])->name('idp.approval');
        Route::get('/idp/{id}', [IdpController::class, 'approve'])->name('idp.approve');
        Route::post('idp/revise', [IdpController::class, 'revise'])->name('idp.revise');

        Route::get('/rtc', [RtcController::class, 'approval'])->name('rtc.approval');
        Route::get('/rtc/{id}', [RtcController::class, 'approve'])->name('rtc.approve');
        Route::get('/icp', [IcpController::class, 'approval'])->name('icp.approval');
        Route::get('/icp/{id}', [IcpController::class, 'approve'])->name('icp.approve');
        Route::post('icp/revise', [IcpController::class, 'revise'])->name('icp.revise');
    });

    Route::prefix('employee')->group(function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create'); // Menampilkan form create
        Route::get('employee/check-email', [EmployeeController::class, 'checkEmail'])->name('employee.checkEmail');
        Route::post('/', [EmployeeController::class, 'store'])->name('employee.store'); // Menyimpan data
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit'); // Menampilkan form edit
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employee.update'); // Memperbarui data
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy'); // Menghapus data
        Route::get('/detail/{id}', [EmployeeController::class, 'show'])->name('employee.show'); // Menampilkan detail Employee

        Route::post('/master/import', [EmployeeController::class, 'import'])->name('employee.import');
        Route::post('/status/{id}', [EmployeeController::class, 'status'])->name('employee.status');

        // promotion
        Route::prefix('promotion')->group(function () {
            Route::post('/store', [EmployeeController::class, 'promotionStore'])->name('promotion.store');
            Route::put('/update/{id}', [EmployeeController::class, 'promotionUpdate'])->name('promotion.update');
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
        Route::get('/{company?}', [EmployeeController::class, 'index'])->name('employee.index');
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
        Route::get('/summary/{id?}', [RtcController::class, 'summary'])->name('rtc.summary');
        Route::get('/detail', [RtcController::class, 'detail'])->name('rtc.detail');
        Route::get('/list/{id?}', [RtcController::class, 'list'])->name('rtc.list');
        Route::get('/update', [RtcController::class, 'update'])->name('rtc.update');
        Route::get('/{company?}', [RtcController::class, 'index'])->name('rtc.index');

        // routes/web.php
        Route::get('/structure', [RtcController::class, 'summary'])
            ->name('rtc.structure'); // yang sekarang

        // Page khusus chart dabeng (re-use summary backend)
        Route::get('/structure/dabeng', [RtcController::class, 'structureDabeng'])
            ->name('rtc.structure.dabeng');

        // JSON data untuk dabeng (pakai summary yg sama, cukup ?as_json=1)
        Route::get('/structure.json', [RtcController::class, 'summary'])
            ->name('rtc.structure.json'); // panggil dengan ?as_json=1

    });

    Route::prefix('idp')->group(function () {
        // Manager all IDP for President
        Route::get('/manage-all', [IdpController::class, 'manage'])->name('idp.manage.all');
        Route::get('/list/{company?}', [IdpController::class, 'list'])->name('idp.list');
        Route::get('/{company?}', [IdpController::class, 'index'])->name('idp.index');
        Route::post('/idp/store', [IdpController::class, 'store'])->name('idp.store');
        Route::post('/idp/store-mid-year/{employee_id}', [IdpController::class, 'storeOneYear'])->name('idp.storeOneYear');
        Route::post('/send-idp', [IdpController::class, 'sendIdpToSupervisor'])->name('send.idp');
        Route::post('/idp/store-one-year/{employee_id}', [IdpController::class, 'storeMidYear'])->name('idp.storeMidYear');
        Route::get('/development-data/{employee_id}', [IdpController::class, 'showDevelopmentData'])->name('development.data');
        Route::get('/development-mid-data/{employee_id}', [IdpController::class, 'showDevelopmentMidData'])->name('development.mid.data');
        Route::post('/delete/{id}', [IdpController::class, 'destroy'])->name('idp.destroy');
        Route::get('/getData', [IdpController::class, 'getData'])->name('idp.getData');
        Route::get('/export-template/{employee_id}', [IdpController::class, 'exportTemplate'])->name('idp.exportTemplate');
        Route::get('/history/{id}', [IdpController::class, 'show'])->name('idp.show');
        Route::get('/', function () {
            $employee = Employee::all(); // Ambil semua data karyawan
            return view('website.idp.index', [
                'employees' => $employee
            ]);
        });

        Route::get('/edit/{id}', [IdpController::class, 'edit'])->name('idp.edit');
        Route::put('/update/{idp}', [IdpController::class, 'update'])->name('idp.update');
        Route::delete('/delete/{idp}', [IdpController::class, 'deleteIdp'])->name('idp.delete');
    });

    Route::prefix('ipp')->group(function () {
        Route::get('/', [IppController::class, 'index'])->name('ipp.index');
        Route::get('/init', [IppController::class, 'init'])->name('ipp.init');
        Route::post('/', [IppController::class, 'store'])->name('ipp.store');
        Route::post('/submit', [IppController::class, 'submit'])->name('ipp.submit');
        Route::get('/list/{company?}', [IppController::class, 'list'])->name('ipp.list');
        Route::get('/data', [IppController::class, 'listJson'])->name('ipp.list.json');
        Route::delete('/point/{point}', [IppController::class, 'destroyPoint'])->name('ipp.point.destroy');
        Route::get('/export', [IppController::class, 'export'])->name('ipp.export');

        Route::get('/employee/ipps', [IppController::class, 'employeeIppsJson'])
            ->name('ipp.employee.ipps.json');
    });

    Route::prefix('ipa')->group(function () {
        Route::get('/', [IppController::class, 'create'])->name('ipa.index');
        Route::post('/', [IppController::class, 'store'])->name('ipa.store');
        Route::get('/list/{company}', [IppController::class, 'list'])->name('ipa.list');
    });

    Route::get('/idp/export-template/{employee_id}', [IdpController::class, 'exportTemplate'])
        ->name('idp.exportTemplate');

    Route::prefix('master')->group(function () {
        Route::get('/filter', [MasterController::class, 'filter'])->name('filter.master');
        Route::get('/employee/{company?}', [MasterController::class, 'employee'])->name('employee.master.index');
        Route::get('/assesment', [MasterController::class, 'assesment'])->name('assesment.master.index');

        Route::prefix('plant')->group(function () {
            Route::get('/{company?}', [PlantController::class, 'plant'])->name('plant.master.index');
            Route::post('/store', [PlantController::class, 'Store'])->name('plant.master.store');
            Route::put('/update/{id}', [PlantController::class, 'update'])->name('plant.master.update');

            Route::delete('/delete/{id}', [PlantController::class, 'plantDestroy'])->name('plant.master.destroy');
        });

        Route::prefix('department')->group(function () {
            Route::get('/{company?}', [MasterController::class, 'department'])->name('department.master.index');
            Route::post('/store', [MasterController::class, 'departmentStore'])->name('department.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'departmentDestroy'])->name('department.master.destroy');
            Route::get('/get-managers/{company}', [MasterController::class, 'getManagers']);
            Route::put('/update/{id}', [MasterController::class, 'departmentUpdate'])->name('department.master.update');
        });

        Route::prefix('division')->group(function () {
            Route::get('/{company?}', [MasterController::class, 'division'])->name('division.master.index');
            Route::post('/store', [MasterController::class, 'divisionStore'])->name('division.master.store');
            Route::put('/master/update/{id}', [MasterController::class, 'updateDivision'])->name('division.master.update');

            Route::delete('/delete/{id}', [MasterController::class, 'divisionDestroy'])->name('division.master.destroy');
        });


        Route::prefix('section')->group(function () {
            Route::get('/{company?}', [MasterController::class, 'section'])->name('section.master.index');
            Route::post('/store', [MasterController::class, 'sectionStore'])->name('section.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'sectionDestroy'])->name('section.master.destroy');
            Route::put('/update/{id}', [MasterController::class, 'sectionUpdate'])->name('section.master.update');
        });

        Route::prefix('subSection')->group(function () {
            Route::get('/{company?}', [MasterController::class, 'subSection'])->name('subSection.master.index');
            Route::post('/store', [MasterController::class, 'subSectionStore'])->name('subSection.master.store');

            Route::put('/update/{id}', [MasterController::class, 'subSectionUpdate'])->name('sub-section.master.update');

            Route::delete('/delete/{id}', [MasterController::class, 'subSectionDestroy'])->name('subSection.master.destroy');
        });

        Route::prefix('users')->group(function () {
            Route::get('/{company?}', [MasterController::class, 'users'])->name('users.master.index');
        });

        Route::prefix('grade')->group(function () {
            Route::get('/', [MasterController::class, 'grade'])->name('grade.master.index');
            Route::post('/store', [MasterController::class, 'gradeStore'])->name('grade.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'gradeDestroy'])->name('grade.master.destroy');
        });
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');
    Route::get('/change-password', [PasswordController::class, 'showChangeForm'])->name('change-password.auth');
    Route::post('/change-password', [PasswordController::class, 'changeForm'])->name('change-password.post');

    Route::prefix('users')->group(function () {
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('users.master.edit');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('users.master.update');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('users.master.destroy');
    });
});
