<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/search', [SearchController::class, 'search']);
    Route::post('/register', [AuthenticationController::class, 'register_account']);
    Route::post('/login', [AuthenticationController::class, 'authenticate']);

    //
    Route::resource('/posts', PostController::class);
    Route::get('account-exists', [AccountController::class, 'checkAccountExists']);
    Route::post('account-update', [AccountController::class, 'updateAccount']);


    //
    Route::middleware(['auth:sanctum'])->group(function (){
       Route::post('/logout', [AuthenticationController::class, 'logout']);
       Route::post('account-posts', [AccountController::class, 'posts']);

       //User Profile
    });


    //Getting file path
    Route::get('/{segments}', function ($filePath) {
        $path = storage_path('app/' .   $filePath);

        if (!file_exists($path)) {
            abort(404); 
        }

        return response()->file($path);
    })->where('segments', '.*');
});