<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\ApplicationController;
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

//Route::group([], function () {
//    Route::resource('student', StudentController::class)->names('student');
//});


    Route::group(
        [
            'middleware' => [Authenticate::class, ],
            'as' => 'student.',
        ], function () {
        Route::get('/applications', [ApplicationController::class, 'applications'])->name('home');
        Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
        Route::get('/profile', [StudentController::class, 'index'])->name('profile');
        Route::post('/update', [StudentController::class, 'store'])->name('store');
        Route::put('/update', [StudentController::class, 'update'])->name('update');

        Route::get('/student/api/fetch/students/applications', [StudentController::class, 'fetchApplications'])->name('claims.fetchApplications');
        Route::get('/student/api/fetch/institutions/{institution?}', [StudentController::class, 'fetchInstitutions'])->name('claims.fetchInstitutions');

    });
