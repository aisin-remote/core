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
use App\Http\Controllers\ToDoListController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\GroupCompetencyController;
use App\Http\Controllers\EmployeeCompetencyController;
use App\Http\Controllers\ChecksheetAssessmentController;
use App\Http\Controllers\SkillMatrixController;
use App\Http\Controllers\ChecksheetUserController;
use App\Http\Controllers\EvaluationController;
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
Route::get('/checksheet-assessment/{employeeId}/{competencyId}', [ChecksheetAssessmentController::class, 'index'])
    ->where(['employeeId' => '[0-9]+', 'competencyId' => '[0-9]+'])
    ->name('checksheet-assessment.index');
Route::get('/checksheet-assessment/view/{employeeCompetencyId}', [ChecksheetAssessmentController::class, 'show'])
     ->name('checksheet-assessment.view');
Route::post('/checksheet-assessment', [ChecksheetAssessmentController::class, 'store'])
     ->name('checksheet-assessment.store');
Route::get('/checksheet-assessment/history/{employeeId}/{competencyId}', [ChecksheetAssessmentController::class, 'competencyHistory'])
    ->name('checksheet-assessment.competency-history');

/* Checksheet User */
Route::get('/checksheet_user', [ChecksheetUserController::class, 'index'])->name('checksheet_user.index');
Route::post('/checksheet_user/create', [ChecksheetUserController::class, 'create'])->name('checksheet_user.create');
Route::post('/checksheet_user/store', [ChecksheetUserController::class, 'store'])->name('checksheet_user.store');
Route::delete('/checksheet_user/{checksheetUser}', [ChecksheetUserController::class, 'destroy'])->name('checksheet_user.destroy');
Route::get('/get-competencies', [ChecksheetUserController::class, 'getCompetencies'])->name('get.competencies');

/* Evaluation */
Route::get('/evaluation/checksheet-users/{employeeId}/{competencyId}', [EvaluationController::class,'getChecksheetUsers'])
    ->name('evaluation.checksheet-users');
Route::get('/evaluation/{employee_competency_id}', [EvaluationController::class,'index'])
    ->name('evaluation.index');
Route::post('/evaluation', [EvaluationController::class,'store'])
    ->name('evaluation.store');
Route::get('/evaluation/view/{employeeCompetencyId}', [EvaluationController::class,'view'])
    ->name('evaluation.view');
Route::post('/evaluation/update-scores/{employee_competency_id}', [EvaluationController::class, 'updateScores'])
    ->name('evaluation.updateScores');

/* Skill Matrix */
Route::middleware(['auth'])->group(function () {
    Route::get('skill-matrix', [SkillMatrixController::class, 'index'])
         ->name('skillMatrix.index');
    Route::get('skill-matrix/{id}', [SkillMatrixController::class, 'show'])
         ->name('skillMatrix.show')
         ->where('id', '[0-9]+');
    Route::get('skill-matrix/{employeeCompetencyId}/checksheet', 
         [SkillMatrixController::class, 'checksheet'])
         ->name('skillMatrix.checksheet')
         ->where('employeeCompetencyId', '[0-9]+');
    Route::post('skill-matrix/{id}/upload-evidence', [SkillMatrixController::class, 'uploadEvidence'])
        ->name('skillMatrix.uploadEvidence')
        ->where('id', '[0-9]+');
});

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

    Route::prefix('todolist')->group(function () {
        Route::get('/', [ToDoListController::class, 'index'])->name('todolist.index');
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
        Route::get('/list-approval-HAV', [HavController::class, 'approval'])->name('hav.approval');
        Route::get('/list-approval-IDP', [IdpController::class, 'approvalidp'])->name('idp.approvalidp');
        Route::get('/skill-matrix', [SkillMatrixController::class, 'approval'])
             ->name('skillMatrix.approval');
        Route::post('/skill-matrix/{id}/approve', [SkillMatrixController::class, 'approve'])
             ->name('skillMatrix.approve')
             ->where('id','[0-9]+');
        Route::post('/skill-matrix/{id}/unapprove', [SkillMatrixController::class, 'unapprove'])
             ->name('skillMatrix.unapprove')
             ->where('id','[0-9]+');
    });

    Route::prefix('approval')->group(function () {
        Route::get('/hav', [HavController::class, 'approval'])->name('hav.approval');
        Route::patch('/hav/approve/{id}', [HavController::class, 'approve'])->name('hav.approve');
        Route::patch('/hav/reject/{id}', [HavController::class, 'reject'])->name('hav.reject');
        Route::get('/idp', [IdpController::class, 'approval'])->name('idp.approval');
        Route::get('/idp/{id}', [IdpController::class, 'approve'])->name('idp.approve');
        Route::post('idp/revise', [IdpController::class, 'revise'])->name('idp.revise');
        Route::get('/skill-matrix', [SkillMatrixController::class, 'approval'])
        ->name('skillMatrix.approval');
        Route::post('/skill-matrix/{id}/approve', [SkillMatrixController::class, 'approve'])
            ->name('skillMatrix.approve')
            ->where('id','[0-9]+');
        Route::post('/skill-matrix/{id}/unapprove', [SkillMatrixController::class, 'unapprove'])
            ->name('skillMatrix.unapprove')
            ->where('id','[0-9]+');
    });

    Route::prefix('employee')->group(function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create'); // Menampilkan form create

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
            Route::put('/update/{id}', [EmployeeController::class, 'promotionUpdate'])->name('promotion.update');
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
        Route::get('/summary', [RtcController::class, 'summary'])->name('rtc.summary');
        Route::get('/detail', [RtcController::class, 'detail'])->name('rtc.detail');
        Route::get('/list', [RtcController::class, 'list'])->name('rtc.list');
        Route::get('/update', [RtcController::class, 'update'])->name('rtc.update');
        Route::get('/{company?}', [RtcController::class, 'index'])->name('rtc.index');
    });

    Route::prefix('idp')->group(function () {
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
            Route::put('/update/{id}', [PlantController::class, 'update'])->name('plant.master.update');

            Route::delete('/delete/{id}', [PlantController::class, 'plantDestroy'])->name('plant.master.destroy');
        });

        Route::prefix('department')->group(function () {
            Route::get('/', [MasterController::class, 'department'])->name('department.master.index');
            Route::post('/store', [MasterController::class, 'departmentStore'])->name('department.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'departmentDestroy'])->name('department.master.destroy');
            Route::get('/get-managers/{company}', [MasterController::class, 'getManagers']);
            Route::put('/update/{id}', [MasterController::class, 'departmentUpdate'])->name('department.master.update');
        });

        Route::prefix('division')->group(function () {
            Route::get('/', [MasterController::class, 'division'])->name('division.master.index');
            Route::post('/store', [MasterController::class, 'divisionStore'])->name('division.master.store');
            Route::put('/master/update/{id}', [MasterController::class, 'updateDivision'])->name('division.master.update');

            Route::delete('/delete/{id}', [MasterController::class, 'divisionDestroy'])->name('division.master.destroy');
        });


        Route::prefix('section')->group(function () {
            Route::get('/', [MasterController::class, 'section'])->name('section.master.index');
            Route::post('/store', [MasterController::class, 'sectionStore'])->name('section.master.store');
            Route::delete('/delete/{id}', [MasterController::class, 'sectionDestroy'])->name('section.master.destroy');
            Route::put('/update/{id}', [MasterController::class, 'sectionUpdate'])->name('section.master.update');
        });

        Route::prefix('subSection')->group(function () {
            Route::get('/', [MasterController::class, 'subSection'])->name('subSection.master.index');
            Route::post('/store', [MasterController::class, 'subSectionStore'])->name('subSection.master.store');

            Route::put('/update/{id}', [MasterController::class, 'subSectionUpdate'])->name('sub-section.master.update');

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
