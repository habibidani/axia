<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\Internal\McpController;
use App\Http\Controllers\Api\RunController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookController;
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

Route::middleware('auth:sanctum')->group(function () {
    
    // User & Company
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::get('/company', [UserController::class, 'company']);
    });
    
    // Goals & KPIs
    Route::prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/', [GoalController::class, 'store']);
        Route::get('/{goal}', [GoalController::class, 'show']);
        Route::put('/{goal}', [GoalController::class, 'update']);
        Route::delete('/{goal}', [GoalController::class, 'destroy']);
        
        // KPIs for a specific goal
        Route::get('/{goal}/kpis', [GoalController::class, 'kpis']);
        Route::post('/{goal}/kpis', [GoalController::class, 'storeKpi']);
    });
    
    // Todos & Runs
    Route::prefix('runs')->group(function () {
        Route::get('/', [RunController::class, 'index']);
        Route::get('/{run}', [RunController::class, 'show']);
        Route::get('/{run}/todos', [RunController::class, 'todos']);
        Route::get('/{run}/evaluations', [RunController::class, 'evaluations']);
        Route::get('/{run}/missing-todos', [RunController::class, 'missingTodos']);
    });
    
    Route::prefix('todos')->group(function () {
        Route::post('/', [TodoController::class, 'store']);
        Route::post('/batch', [TodoController::class, 'storeBatch']);
    });
    
    // Chat endpoints
    Route::prefix('chat')->group(function () {
        Route::post('/start', [ChatController::class, 'start']);
        Route::post('/message', [ChatController::class, 'message']);
        Route::get('/session/{sessionId}', [ChatController::class, 'show']);
    });
    
    // Webhooks (for n8n to trigger)
    Route::prefix('webhooks')->group(function () {
        Route::post('/run-completed', [WebhookController::class, 'runCompleted']);
        Route::post('/goal-achieved', [WebhookController::class, 'goalAchieved']);
    });
});

// Public webhook endpoint (with signature verification)
Route::post('/webhooks/n8n/incoming', [WebhookController::class, 'incomingWebhook']);

// Internal MCP API (protected by shared secret)
Route::prefix('internal/mcp')->middleware('verify.mcp.secret')->group(function () {
    Route::get('/health', [McpController::class, 'health']);
    Route::post('/context', [McpController::class, 'getContext']);
    Route::post('/emails', [McpController::class, 'getEmails']);
    Route::post('/todos/create', [McpController::class, 'createTodos']);
    Route::post('/goals/update', [McpController::class, 'updateGoal']);
});
