<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\CadreController;
use App\Http\Controllers\Api\PosyanduController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\BrideController;
use App\Http\Controllers\Api\DashboardController;

// Auth Endpoint
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('config', ConfigController::class)->only(['index','store']);
});

// Dashboard Endpoint
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/posyandu/{id}/wilayah', [DashboardController::class, 'getPosyanduWilayah']);


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
    Route::get('/cadre/pending', [CadreController::class, 'pendingData']);
    Route::apiResource('cadre', CadreController::class)
        ->only(['index','store','show','update'])
        ->where(['cadre' => '[0-9]+']);

    Route::put('/cadre/deactive/{id}', [CadreController::class, 'deactive']);
    Route::put('/cadre/active/{id}', [CadreController::class, 'active']);
    Route::put('/cadre/delete/{id}', [CadreController::class, 'delete']);
});

// Posyandu Endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/posyandu', [PosyanduController::class, 'getPosyandu']);
    Route::get('/posyandu/{id_wilayah}', [PosyanduController::class, 'getByWilayah']);
});

// Member Endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('member', MemberController::class)
        ->only(['index','store','show'])
        ->where(['member' => '[0-9]+']);
    Route::get('/member/tpk', [MemberController::class, 'getTPK']);
    Route::get('/member/user', [MemberController::class, 'getUser']);
    Route::post('/member/assign', [MemberController::class, 'assign']);
    Route::get('/member/tpk/{no_tpk}', [MemberController::class, 'memberTPK']);
    Route::get('/member/{id}/family', [MemberController::class, 'family']);
});

// Bride Endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bride/pending', [BrideController::class, 'pendingData']);
    Route::apiResource('bride', BrideController::class)
        ->only(['index', 'store', 'show']);
    Route::get('/bride/check', [BrideController::class, 'checkDampinganKe']);
    Route::get('/bride/search/{nik}', [BrideController::class, 'search']);
    Route::get('/bride/{id}/pending', [BrideController::class, 'pending']);
    Route::put('/bride/{id}', [BrideController::class, 'update']);
});

// Region Endpoint
Route::apiResource('region', RegionController::class)->only(['index','store']);
Route::get('/region/provinsi', [RegionController::class, 'getProvinsi']);
Route::get('/region/kota', [RegionController::class, 'getKota']);
Route::get('/region/kecamatan', [RegionController::class, 'getKecamatan']);
Route::get('/region/kelurahan', [RegionController::class, 'getKelurahan']);

// Log Endpoint
Route::middleware('auth:sanctum')->get('/log', [LogController::class, 'index']);

// User Endpoint
Route::middleware('auth:sanctum')->get('/user/region', [CadreController::class, 'wilayahByUser']);
