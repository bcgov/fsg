<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Modules\Ministry\Http\Controllers\AllocationController;
use Modules\Ministry\Http\Controllers\ClaimController;
use Modules\Ministry\Http\Controllers\InstitutionController;
use Modules\Ministry\Http\Controllers\InstitutionStaffController;
use Modules\Ministry\Http\Controllers\MaintenanceController;
use Modules\Ministry\Http\Controllers\ProgramController;
use Modules\Ministry\Http\Controllers\StudentController;
use Modules\Ministry\Http\Middleware\IsActive;

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

Route::prefix('ministry')->group(function () {
    Route::group(
        [
            'middleware' => [Authenticate::class, IsActive::class],
            'as' => 'ministry.',
        ], function () {
            Route::get('/', [InstitutionController::class, 'index'])->name('home');
            Route::get('/institutions', [InstitutionController::class, 'index'])->name('institutions.index');
            Route::get('/institutions/{institution}/{page?}', [InstitutionController::class, 'show'])->name('institutions.show');
            Route::put('/institutions', [InstitutionController::class, 'update'])->name('institutions.update');

            Route::get('/students', [StudentController::class, 'index'])->name('students.index');
            Route::get('/students/{student}/{page?}', [StudentController::class, 'show'])->name('students.show');
            Route::put('/students', [StudentController::class, 'update'])->name('students.update');

            Route::put('/programs', [ProgramController::class, 'update'])->name('programs.update');
            Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');

            Route::put('/allocations', [AllocationController::class, 'update'])->name('allocations.update');
            Route::post('/allocations', [AllocationController::class, 'store'])->name('allocations.store');

            Route::put('/claims', [ClaimController::class, 'update'])->name('claims.update');
            Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');

            Route::get('/api/fetch/claims/{guid?}', [ClaimController::class, 'fetchClaims'])->name('institutions.fetchClaims');
            Route::get('/api/fetch/programs', [InstitutionController::class, 'fetchPrograms'])->name('institutions.fetchPrograms');
            Route::get('/api/fetch/students/claims-by-student', [ClaimController::class, 'fetchStudentsClaims'])->name('claims.fetchStudentsClaims');
            Route::get('/api/fetch/institutions/claims-by-course', [ClaimController::class, 'fetchClaimsByCourse'])->name('claims.fetchClaimsByCourse');
            Route::get('/api/fetch/institutions/claims-by-student', [ClaimController::class, 'fetchClaimsByStudent'])->name('claims.fetchClaimsByStudent');

            Route::group([
                'middleware' => ['ministry_admin'],
            ], function () {
                Route::group([
                    'prefix' => 'maintenance',
                    'as' => 'maintenance.',
                ], function () {

                    Route::get('/staff', [MaintenanceController::class, 'staffList'])->name('staff.list');

                    Route::put('/staff/{user}', [MaintenanceController::class, 'updateStatus'])->name('staff.status.update');
                    Route::put('/staff/roles/{user}', [MaintenanceController::class, 'updateRole'])->name('staff.roles.update');

                    Route::get('/utils', [MaintenanceController::class, 'utilList'])->name('utils.list');
                    Route::put('/utils/{util}', [MaintenanceController::class, 'utilUpdate'])->name('utils.update');
                    Route::post('/utils', [MaintenanceController::class, 'utilStore'])->name('utils.store');

                    Route::get('/program_years', [MaintenanceController::class, 'pyList'])->name('program_years.list');
                    Route::put('/program_years/{programYear}', [MaintenanceController::class, 'pyUpdate'])->name('program_years.update');
                    Route::post('/program_years', [MaintenanceController::class, 'pyStore'])->name('program_years.store');

                });

                Route::put('/institution_staff', [InstitutionStaffController::class, 'update'])->name('institution_staff.update');
                Route::put('/institution_roles', [InstitutionStaffController::class, 'updateRole'])->name('institution_roles.updateRole');
                Route::get('/institution_login/{guid}', [InstitutionStaffController::class, 'ministryLogin'])->name('institution_login.login');

                Route::get('/reports/summary', [MaintenanceController::class, 'reportsSummary'])->name('reports.summary');
                Route::post('/reports/summary', [MaintenanceController::class, 'reportsSummaryFetch'])->name('reports.summary.fetch');
//                Route::get('/reports/detail', [MaintenanceController::class, 'reportsDetail'])->name('reports.detail');
//                Route::post('/reports/detail', [MaintenanceController::class, 'reportsDetailFetch'])->name('reports.detail.fetch');

                Route::get('/reports/sources', [MaintenanceController::class, 'reportSources'])->name('reports.sources');
                Route::get('/reports/sources-download/{from}/{to}/{type}', [MaintenanceController::class, 'reportSourcesFetch'])->name('reports.sources.fetch');

            });
        });
});
