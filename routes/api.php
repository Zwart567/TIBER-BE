<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PersonalizationController;

// Login, logout, register
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);

// dashboard
Route::middleware('auth:sanctum')->get('/dashboard',[DashboardController::class, 'dashboard']);
Route::middleware('auth:sanctum')->post('/log',[DashboardController::class, 'log']);

//Profile
Route::middleware('auth:sanctum')->put('/user',[ProfileController::class, 'user']);

//Personalization
Route::middleware('auth:sanctum')->get('/personalization',[PersonalizationController::class,'personalization']);