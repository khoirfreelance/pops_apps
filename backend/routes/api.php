<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\CadreController;

// Auth Endpoint
Route::post('/login', [AuthController::class, 'login']);
Route::get('/config', [ConfigController::class, 'index']);
Route::middleware('auth:sanctum')->post('/config', [ConfigController::class, 'store']);

// Family Endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('family', FamilyController::class)->only(['index','store']);
    Route::get('/family/detail/{no_kk}', [FamilyController::class, 'detail']);
    Route::get('/family/check', [FamilyController::class, 'checkNoKK']);
    Route::post('/family/import', [FamilyController::class, 'import']);
    Route::get('/family/pending', [FamilyController::class, 'pendingData']);
    Route::get('/family/{id}/pending', [FamilyController::class, 'pending']);
    Route::put('/family/{id}', [FamilyController::class, 'update']);
});

// Cadre Endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cadre', CadreController::class)->only(['index','store']);
    Route::get('/cadre/pending', [CadreController::class, 'pendingData']);
    /* Route::get('/family/detail/{no_kk}', [FamilyController::class, 'detail']);
    Route::get('/family/check', [FamilyController::class, 'checkNoKK']);
    Route::post('/family/import', [FamilyController::class, 'import']);
    Route::get('/family/pending', [FamilyController::class, 'pendingData']);
    Route::get('/family/{id}/pending', [FamilyController::class, 'pending']);
    Route::put('/family/{id}', [FamilyController::class, 'update']); */
});


// Region Endpoint
Route::apiResource('region', RegionController::class)->only(['index','store']);
Route::get('/region/provinsi', [RegionController::class, 'getProvinsi']);
Route::get('/region/kota', [RegionController::class, 'getKota']);
Route::get('/region/kecamatan', [RegionController::class, 'getKecamatan']);
Route::get('/region/kelurahan', [RegionController::class, 'getKelurahan']);

// Log Endpoint
Route::middleware('auth:sanctum')->get('/log', [LogController::class, 'index']);
