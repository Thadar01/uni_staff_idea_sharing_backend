<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\StaffAuthController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RolePermissionController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ClosureSettingController;
use App\Http\Controllers\API\IdeaController;
use App\Http\Controllers\API\IdeaCategoryController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\VoteController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\Api\SystemReportController;

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
    Route::post('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::post('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);
    Route::get('/departments/{id}/staffs', [DepartmentController::class, 'getStaffByDepartment']);

    // Staffs
    Route::get('/staffs', [StaffController::class, 'index']);
    Route::get('/staffs/{id}', [StaffController::class, 'show']);
    Route::post('/staffs', [StaffController::class, 'store']);
    Route::post('/staffs/{id}', [StaffController::class, 'update']);
    Route::delete('/staffs/{id}', [StaffController::class, 'destroy']);
    Route::patch('/staffs/{id}/status', [StaffController::class, 'updateStatus']);
    Route::patch('/staffs/{id}/hide-content', [StaffController::class, 'hideContent']);
    Route::patch('/staffs/{id}/unhide-content', [StaffController::class, 'unhideContent']);
    
    // System Reports (Usage Monitoring)
    Route::get('/system-reports/usage', [SystemReportController::class, 'getUsageStats']);

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    // Route::post('/permissions', [PermissionController::class, 'store']);
    // Route::post('/permissions/{id}', [PermissionController::class, 'update']);
    // Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    // Role Permissions
    Route::get('/role-permissions', [RolePermissionController::class, 'index']);
    Route::get('/role-permissions/{id}', [RolePermissionController::class, 'show']);
    Route::post('/roles/{roleID}/permissions', [RolePermissionController::class, 'givePermission']);
    Route::delete('/role-permissions/{id}', [RolePermissionController::class, 'destroy']);

     // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Closure Settings
    Route::get('/closure-settings', [ClosureSettingController::class, 'index']);
    Route::get('/closure-settings/{id}', [ClosureSettingController::class, 'show']);
    Route::post('/closure-settings', [ClosureSettingController::class, 'store']);
    Route::post('/closure-settings/{id}', [ClosureSettingController::class, 'update']);
    Route::delete('/closure-settings/{id}', [ClosureSettingController::class, 'destroy']);
    Route::get('/closure-settings/{id}/download-documents', [ClosureSettingController::class, 'downloadZip']);
    Route::get('/closure-settings/{id}/export-ideas', [ClosureSettingController::class, 'exportIdeas']);

    // Ideas
Route::get('/ideas', [IdeaController::class, 'index']);
Route::get('/ideas/{id}', [IdeaController::class, 'show']);
Route::post('/ideas', [IdeaController::class, 'store']);
Route::post('/ideas/{id}', [IdeaController::class, 'update']);
Route::patch('/ideas/{id}', [IdeaController::class, 'update']);
Route::delete('/ideas/{id}', [IdeaController::class, 'destroy']);
Route::post('/ideas/{id}/status', [IdeaController::class, 'updateStatus']);
Route::post('/ideas/{id}/only-status', [IdeaController::class, 'updateOnlyStatus']);
Route::post('/ideas/{id}/increase-view', [IdeaController::class, 'increaseViewCount']);
Route::patch('/ideas/{id}/hide', [IdeaController::class, 'hide']);
Route::patch('/ideas/{id}/unhide', [IdeaController::class, 'unhide']);
  
    // Comments
    Route::get('/comments', [CommentController::class, 'index']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::post('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::patch('/comments/{id}/hide', [CommentController::class, 'hide']);
    Route::patch('/comments/{id}/unhide', [CommentController::class, 'unhide']);

    // Votes
    Route::get('/votes', [VoteController::class, 'index']);
    Route::get('/votes/{id}', [VoteController::class, 'show']);
    Route::post('/votes', [VoteController::class, 'store']);
    Route::post('/votes/{id}', [VoteController::class, 'update']);
    Route::delete('/votes/{id}', [VoteController::class, 'destroy']);

    Route::get('/reports', [ReportController::class, 'index']);
Route::get('/reports/{id}', [ReportController::class, 'show']);
Route::post('/reports', [ReportController::class, 'store']);
Route::put('/reports/{id}', [ReportController::class, 'update']);
Route::get('/reports/department/{id}', [ReportController::class, 'getReportsByDepartment']);
// Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
