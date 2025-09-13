<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PaginationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Pagination utility endpoints (public for frontend integration)
Route::get('/pagination/options', [PaginationController::class, 'getOptions']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Company routes
    Route::apiResource('companies', CompanyController::class);
    
    // Employee routes
    Route::apiResource('employees', EmployeeController::class);
    
    });