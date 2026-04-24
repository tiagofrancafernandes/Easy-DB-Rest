<?php

use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/health', HealthController::class);

Route::post('/connection/test', [QueryController::class, 'test']);

Route::apiResource('/connections', ConnectionController::class);

Route::prefix('/query')->group(function (): void {
    Route::post('/', [QueryController::class, 'query']);
    Route::post('/raw', [QueryController::class, 'raw']);
    Route::post('/builder', [QueryController::class, 'builder']);
});
