<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

Route::get('/logout', [UserController::class, 'logout'])->name('get-logout');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [UserController::class, 'login'])->name('login');
Route::get('/applogin', [UserController::class, 'appLogin'])->name('app-login');
Route::get('/bceid-login', [UserController::class, 'bceidLogin'])->name('bceid-login');
Route::get('/bcsc-login', [UserController::class, 'bcscLogin'])->name('bcsc-login');
Route::middleware(['auth'])->get('/home', [UserController::class, 'home'])->name('home');


Route::get('/test-redis', function () {
    try {
        $name = Redis::get('name');
        return "Redis connection successful. Name: " . $name;
    } catch (Exception $e) {
        return "Redis connection failed: " . $e->getMessage();
    }
});
Redis::connection();
