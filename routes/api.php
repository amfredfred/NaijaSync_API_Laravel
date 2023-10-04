<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SegmentsController;
use App\Http\Controllers\SystemSetingsController;
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
    // Public
    Route::get('search', [SearchController::class, 'search']);
    Route::post('register', [AuthenticationController::class, 'register_account']);
    Route::post('login', [AuthenticationController::class, 'authenticate']);
    Route::get('account-exists', [AccountController::class, 'checkAccountExists']);
    // Systems Settings/Configurations
    Route::get('system-configs', [SystemSetingsController::class, '']);
    //Posts
    Route::resource('posts', PostController::class);
    Route::post('post-viewed', [PostController::class, 'postView']);
    Route::post('post-react', [PostController::class, 'postReact']);
    Route::post('post-reward', [PostController::class, 'postReward']);

    //When authenticated
    Route::middleware(['auth:sanctum'])->group(function (){
       Route::post('logout', [AuthenticationController::class, 'logout']);
       Route::post('account-posts', [AccountController::class, 'posts']);
       Route::post('account-update', [AccountController::class, 'updateAccount']);
       Route::post('account-info', [AccountController::class, 'accountInfo']);
       //User Profile
    });


    //Getting file path
    Route::get('/{segments}', [SegmentsController::class])->where('segments', '.*');
});