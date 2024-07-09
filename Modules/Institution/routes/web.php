<?php

use Illuminate\Support\Facades\Route;
use Modules\Institution\Http\Controllers\InstitutionController;

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


        Route::post('/program_years/default', [ProgramYearController::class, 'setDefault'])->name('program_years.set-default');

    });

    Route::group([
        'middleware' => ['institution_admin'],
    ], function () {

    });
});
