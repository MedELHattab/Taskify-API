<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('tasks/store', [TaskController::class, 'store'])->middleware(['auth:sanctum']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('tasks/show/{task}', [TaskController::class, 'show'])->middleware(['auth:sanctum', 'can:view,task']);

Route::get('tasks/index', [TaskController::class, 'index'])->middleware(['auth:sanctum', 'can:viewAny,App\Models\Task']);

Route::delete('tasks/destroy/{task}', [TaskController::class, 'destroy'])->middleware(['auth:sanctum','can:delete,task']);

Route::put('tasks/update/{task}', [TaskController::class, 'update'])->middleware(['auth:sanctum','can:update,task']);
