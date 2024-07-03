<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
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

Route::group([], function () {
    Route::resource('student', StudentController::class)->names('student');
});


Route::prefix('student')->group(function () {
    Route::group(
        [
            'middleware' => [Authenticate::class, ],
            'as' => 'student.',
        ], function () {
        Route::get('/', [StudentController::class, 'index'])->name('home');

    });
});
