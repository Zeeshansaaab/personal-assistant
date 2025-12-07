<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LendingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SplitExpenseController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/user/currency', [AuthController::class, 'updateCurrency']);

    // FCM Token Registration
    Route::post('/fcm/register', [FcmController::class, 'registerToken']);

    // Tasks & Reminders
    Route::apiResource('tasks', TaskController::class);

    // Money Lending & Item Tracking
    Route::apiResource('lendings', LendingController::class);
    Route::post('/lendings/{id}/mark-returned', [LendingController::class, 'markReturned']);
    Route::get('/lendings/check-due-returns', [LendingController::class, 'checkDueReturns']);

    // Expense Tracker
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('/expenses/summary/monthly', [ExpenseController::class, 'monthlySummary']);
    Route::get('/expenses/chart/data', [ExpenseController::class, 'chartData']);

    // Groups & Collaboration
    Route::apiResource('groups', GroupController::class);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember']);
    Route::delete('/groups/{id}/members/{userId}', [GroupController::class, 'removeMember']);
    Route::get('/users/search', [GroupController::class, 'searchUsers']);

    // Split Expenses
    Route::get('/groups/{groupId}/expenses', [SplitExpenseController::class, 'index']);
    Route::post('/groups/{groupId}/expenses', [SplitExpenseController::class, 'store']);
    Route::put('/groups/{groupId}/expenses/{expenseId}', [SplitExpenseController::class, 'update']);
    Route::get('/groups/{groupId}/balance', [SplitExpenseController::class, 'getUserBalance']);
    Route::get('/groups/{groupId}/settlements', [SplitExpenseController::class, 'getSettlements']);
});

