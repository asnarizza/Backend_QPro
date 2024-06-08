<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentCounterController;
use App\Http\Controllers\CustomerQueueController;

// users
Route::post('signup', [UserController::class, 'signup']);
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout']);
Route::get('/staff', [UserController::class, 'getStaff']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->post('logout', [UserController::class, 'logout']);

// counters
Route::post('add-counters', [CounterController::class, 'store']);
Route::get('counters', [CounterController::class, 'index']);
Route::delete('/counters/{counters}', [CounterController::class, 'delete']);

// departments
Route::post('add-departments', [DepartmentController::class, 'store']);
Route::get('departments', [DepartmentController::class, 'index']);
Route::delete('/departments/{department}', [DepartmentController::class, 'delete']);

// department_counters
Route::put('add-department-counters', [DepartmentCounterController::class, 'update']);
Route::get('/department-counter/{staff_id}', [DepartmentCounterController::class, 'getDepartmentCounter']);
Route::post('/assign-new-counter-id', [DepartmentCounterController::class, 'assignNewCounterId']);
Route::get('counters-by-department/{department_id}', [DepartmentCounterController::class, 'getCountersByDepartmentId']);

// customer_queues
Route::post('customer-queues/generate', [CustomerQueueController::class, 'store']);
Route::put('/call-queue/{queue}/{department_id}/{counter_id}', [CustomerQueueController::class, 'callQueue'])->name('call-queue');
Route::post('/update-current-queue', [CustomerQueueController::class, 'updateCurrentQueue'])->name('update-current-queue');
Route::post('customer-queue/pass/{queue_number}/{new_department_id}', [CustomerQueueController::class, 'passQueue'])->name('pass-queue');
Route::get('queue-number/{departmentId}', [CustomerQueueController::class, 'getCurrentQueueByDepartment']);



