<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\RegionController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/config', [ConfigController::class, 'index']);
Route::middleware('auth:sanctum')->post('/config', [ConfigController::class, 'store']);
Route::apiResource('family', FamilyController::class)->only(['index','store']);
Route::apiResource('region', RegionController::class)->only(['index','store']);
Route::get('/region/provinsi', [RegionController::class, 'getProvinsi']);
Route::get('/region/kota', [RegionController::class, 'getKota']);
Route::get('/region/kecamatan', [RegionController::class, 'getKecamatan']);
Route::get('/region/kelurahan', [RegionController::class, 'getKelurahan']);
Route::get('/family/check', [FamilyController::class, 'checkNoKK']);
