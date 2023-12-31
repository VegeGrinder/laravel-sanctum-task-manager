<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureTaskBelongsToUser;
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

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::Class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);

    Route::prefix('tasks/{id}')->middleware(EnsureTaskBelongsToUser::class)->group(function () {
        Route::put('/', [TaskController::class, 'update']);
        Route::delete('/', [TaskController::class, 'destroy']);
        Route::post('/file-upload', [TaskController::class, 'uploadFile']);
        Route::get('/images/{imageId}', [TaskController::class, 'downloadFile']);
        Route::delete('/images/{imageId}', [TaskController::class, 'deleteFile']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
