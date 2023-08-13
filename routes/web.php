<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [FrontController::class, 'login'])->name('login');
Route::get('register', [FrontController::class, 'register'])->name('register');

Route::post('authenticate', [AuthenticationController::class, 'authenticate'])->name('authenticate');
Route::post('register_account', [AuthenticationController::class, 'register_account'])->name('register_account');


Route::middleware(['auth'])->group(function () {
    Route::resource('post', PostController::class);
});