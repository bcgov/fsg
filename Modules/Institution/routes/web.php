<?php

use Illuminate\Support\Facades\Route;
use Modules\Institution\Http\Controllers\ClaimController;
use Modules\Institution\Http\Controllers\InstitutionController;
use Modules\Institution\Http\Controllers\ProgramYearController;
use Modules\Institution\Http\Controllers\StudentController;

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

Route::prefix('institution')->group(function () {
    Route::group(
        [
            'middleware' => ['auth', 'institution_active'],
            'as' => 'institution.',
        ], function () {
            Route::get('/dashboard', [InstitutionController::class, 'index'])->name('dashboard');
            Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
            Route::put('/claims', [ClaimController::class, 'update'])->name('claims.update');
            //        Route::get('/claims/download/{claim}', [ClaimController::class, 'download'])->name('claims.download');
            //        Route::get('/claims/export', [ClaimController::class, 'exportCsv'])->name('claims.export');

            Route::get('/students', [StudentController::class, 'index'])->name('students.index');
            Route::get('/students/{student}/{page?}', [StudentController::class, 'show'])->name('students.show');
            Route::put('/students', [StudentController::class, 'update'])->name('students.update');

            Route::get('/account', [InstitutionController::class, 'show'])->name('show');

            Route::post('/program_years/default', [ProgramYearController::class, 'setDefault'])->name('program_years.set-default');

            Route::get('/api/fetch/claims/{guid?}', [ClaimController::class, 'fetchClaims'])->name('institutions.fetchClaims');
            Route::get('/api/fetch/students/claims-by-student', [ClaimController::class, 'fetchStudentsClaims'])->name('claims.fetchStudentsClaims');

        });

    Route::group([
        'middleware' => ['institution_admin'],
    ], function () {
        Route::get('/staff', [InstitutionController::class, 'staffList'])->name('staff.list');

        Route::put('/staff', [InstitutionController::class, 'staffUpdate'])->name('staff.staffUpdate');
        Route::put('/roles', [InstitutionController::class, 'staffUpdateRole'])->name('roles.staffUpdateRole');

    });
});
