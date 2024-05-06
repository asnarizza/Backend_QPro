<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\CustomerQueueController;


// users
Route::post('signup', [UserController::class, 'signup']);
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->post('logout', [UserController::class, 'logout']);

// Route::post('staff', [StaffController::class, 'store']);

// Route::post('counters', [CounterController::class, 'store']);

// staffs
Route::post('add-staff', [StaffController::class, 'store']);
Route::get('staff/{id}', [StaffController::class, 'show']);
Route::get('staff', [StaffController::class, 'index']);

// counters
Route::post('add-counters', [CounterController::class, 'store']);
Route::get('counters/{id}', [CounterController::class, 'show']);
Route::get('counters', [CounterController::class, 'index']);

// customer_queues
Route::post('customer-queues/generate', [CustomerQueueController::class, 'store']);
Route::put('/mark-as-serviced/{queue}', [CustomerQueueController::class, 'markAsServiced'])->name('mark-as-serviced');
Route::post('/update-current-queue', [CustomerQueueController::class, 'updateCurrentQueue'])->name('update-current-queue');





