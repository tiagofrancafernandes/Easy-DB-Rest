<?php

use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\SnippetController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/health', HealthController::class);

// Public Snippet Route
Route::get('/snippets/{user_id}/{slug}', [SnippetController::class, 'publicView']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/connection/test', [QueryController::class, 'test']);

    Route::apiResource('/connections', ConnectionController::class);
    Route::post('/connections/{connection}/share', [ConnectionController::class, 'share']);

    Route::apiResource('/snippets', SnippetController::class);
    Route::post('/snippets/{snippet}/share', [SnippetController::class, 'share']);

    Route::apiResource('/teams', TeamController::class);
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember']);

    Route::prefix('/query')->group(function (): void {
        Route::post('/', [QueryController::class, 'query']);
        Route::post('/raw', [QueryController::class, 'raw']);
        Route::post('/builder', [QueryController::class, 'builder']);
    });
});
