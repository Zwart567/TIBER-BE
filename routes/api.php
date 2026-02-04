<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Login, logout, register
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);

// dashboard
Route::middleware('auth:sanctum')->get('/dashboard',[AuthController::class, 'dashboard']);
Route::middleware('auth:sanctum')->post('/log',[AuthController::class, 'log']);