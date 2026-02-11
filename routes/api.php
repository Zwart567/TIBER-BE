<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PersonalizationController;
use App\Http\Controllers\Api\ActivityHistory;
use App\Http\Controllers\Api\MonthlyCalendarController;
use App\Http\Controllers\Api\CheckupNote;


// Login, logout, register
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);

// dashboard
Route::middleware('auth:sanctum')->get('/dashboard',[DashboardController::class, 'dashboard']);
Route::middleware('auth:sanctum')->post('/log',[DashboardController::class, 'log']);

//Activity & History
Route::middleware('auth:sanctum')->get('/activity/overview', [ActivityHistory::class, 'ActivityOverview']);
Route::middleware('auth:sanctum')->get('/activity/logs-weekly/{week_start}',[ActivityHistory::class,'WeeklyLog']);

//Profile
Route::middleware('auth:sanctum')->put('/user',[ProfileController::class, 'user']);

//Get Personalization
Route::middleware('auth:sanctum')->get('/personalization',[PersonalizationController::class,'personalization']);

//Get Monthly calendar
Route::middleware('auth:sanctum')->get('/activity/calendar/{month}', [MonthlyCalendarController::class, 'monthlyCalendar']);

//Checkup note get, post, & put
Route::middleware('auth:sanctum')->post('/activity/add-checkup-note',[CheckupNote::class, 'AddCheckupNote']);
Route::middleware('auth:sanctum')->get('/activity/show-checkup-note',[CheckupNote::class, 'GetCheckupNote']);
Route::middleware('auth:sanctum')->put('/activity/edit-checkup-note',[CheckupNote::class, 'PutCheckupNote']);