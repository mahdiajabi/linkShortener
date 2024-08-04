<?php

use App\Http\Controllers\LinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/shorten', [LinkController::class, 'shorten']);
    Route::get('/link/{code}', [LinkController::class, 'redirect']);
    Route::get('/link-click-count', [LinkController::class, 'getClickCount']);
});

use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);
