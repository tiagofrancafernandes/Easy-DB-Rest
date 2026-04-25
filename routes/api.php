<?php

use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\SchemaController;
use App\Http\Controllers\Api\SnippetController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/health', HealthController::class);

// Public Snippet Route
Route::get('/snippets/{user_id}/{slug}', [SnippetController::class, 'publicView']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('connections', ConnectionController::class);
    Route::post('connection/test', [QueryController::class, 'test']);
    Route::post('connections/{connection}/share', [ConnectionController::class, 'share']);

    // Database, Schema, and Table Management (Nested under connections)
    Route::prefix('connections/{connection}')->group(function () {
        Route::get('databases', [DatabaseController::class, 'index']);
        Route::post('databases', [DatabaseController::class, 'store']);
        Route::delete('databases/{name}', [DatabaseController::class, 'destroy']);

        Route::get('schemas', [SchemaController::class, 'index']);
        Route::post('schemas', [SchemaController::class, 'store']);
        Route::delete('schemas/{name}', [SchemaController::class, 'destroy']);

        Route::get('tables', [TableController::class, 'index']);
        Route::post('tables', [TableController::class, 'store']);
        Route::get('tables/{table}', [TableController::class, 'show']);
        Route::put('tables/{table}', [TableController::class, 'update']);
        Route::delete('tables/{table}', [TableController::class, 'destroy']);

        Route::get('views', [ViewController::class, 'index']);
        Route::post('views', [ViewController::class, 'store']);
        Route::delete('views/{name}', [ViewController::class, 'destroy']);
    });

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
