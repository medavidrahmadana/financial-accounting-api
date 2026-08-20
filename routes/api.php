<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\TransactionController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('coas', ChartOfAccountController::class);
Route::apiResource('transactions', TransactionController::class);
