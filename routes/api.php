<?php
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\API\StaffController;

// ======================
// Role Routes
// ======================

Route::get('/roles', [RoleController::class, 'index']);
Route::get('/roles/{id}', [RoleController::class, 'show']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);


// ======================
// Department Routes
// ======================

Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{id}', [DepartmentController::class, 'show']);
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{id}', [DepartmentController::class, 'update']);
Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

//Staff Authentication
Route::post('/staff/login', [StaffAuthController::class, 'login']);
  Route::post('/staff/logout', [StaffAuthController::class, 'logout']);
    Route::post('/staff/refresh', [StaffAuthController::class, 'refresh']);
    Route::get('/staff/me', [StaffAuthController::class, 'me']);


//Staff
   Route::get('/staffs', [StaffController::class, 'index']);
    Route::get('/staffs/{id}', [StaffController::class, 'show']);
    Route::post('/staffs', [StaffController::class, 'store']);
    Route::put('/staffs/{id}', [StaffController::class, 'update']);
    Route::delete('/staffs/{id}', [StaffController::class, 'destroy']);