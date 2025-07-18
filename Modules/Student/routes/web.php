<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\ApplicationController;
use Modules\Student\Http\Controllers\DemographicSharingController;
use Modules\Student\Http\Controllers\StudentController;

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


Route::group(
    [
        'middleware' => [Authenticate::class],
        'as' => 'student.',
    ], function () {
        Route::get('/applications', [ApplicationController::class, 'applications'])->name('home');
        Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
        Route::put('/applications', [ApplicationController::class, 'update'])->name('applications.update');
        Route::get('/profile', [StudentController::class, 'index'])->name('profile');
        Route::post('/profile', [StudentController::class, 'store'])->name('store');
        Route::put('/profile', [StudentController::class, 'update'])->name('update');

        Route::get('/student/api/fetch/students/applications', [ApplicationController::class, 'fetchApplications'])->name('claims.fetchApplications');
        Route::get('/student/api/fetch/institutions/{institution?}', [StudentController::class, 'fetchInstitutions'])->name('claims.fetchInstitutions');

        Route::get('/faqs', [StudentController::class, 'faqList'])->name('faqs.index');

        // Demographic sharing routes
        Route::get('/demographic-sharing', [DemographicSharingController::class, 'index'])->name('demographic_sharing.index');
        Route::post('/demographic-sharing/update', [DemographicSharingController::class, 'updateSharing'])->name('demographic_sharing.update');
        Route::get('/demographic-sharing/summary', [DemographicSharingController::class, 'getSharingSummary'])->name('demographic_sharing.summary');
        Route::post('/demographic-sharing/revoke-all', [DemographicSharingController::class, 'revokeAllSharing'])->name('demographic_sharing.revoke_all');

    });
