<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\HealthController;

Route::get('/', HomeController::class);
Route::get('/health', HealthController::class);
