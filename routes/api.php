<?php
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\StaffAuthController;

// ======================
// Public Routes
// ======================

// Only login is public
Route::post('/staff/login', [StaffAuthController::class, 'login']);

// ======================
// Protected Routes (JWT middleware)
// ======================

    // Staff Auth
    Route::post('/staff/logout', [StaffAuthController::class, 'logout']);
    Route::post('/staff/refresh', [StaffAuthController::class, 'refresh']);
    Route::get('/staff/me', [StaffAuthController::class, 'me']);

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

    // Staffs
    Route::get('/staffs', [StaffController::class, 'index']);
    Route::get('/staffs/{id}', [StaffController::class, 'show']);
    Route::post('/staffs', [StaffController::class, 'store']);
    Route::put('/staffs/{id}', [StaffController::class, 'update']);
    Route::delete('/staffs/{id}', [StaffController::class, 'destroy']);
