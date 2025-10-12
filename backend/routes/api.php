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
    Route::get('/cadre/pending', [CadreController::class, 'pendingData']);
    Route::apiResource('cadre', CadreController::class)
        ->only(['index','store','show','update'])
        ->where(['cadre' => '[0-9]+']);
    Route::get('/posyandu', [PosyanduController::class, 'getPosyandu']);
    Route::put('/cadre/deactive/{id}', [CadreController::class, 'deactive']);
    Route::put('/cadre/active/{id}', [CadreController::class, 'active']);
    Route::put('/cadre/delete/{id}', [CadreController::class, 'delete']);
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
    Route::apiResource('bride', BrideController::class)
        ->only(['index', 'store', 'show']);
});

// Region Endpoint
Route::apiResource('region', RegionController::class)->only(['index','store']);
Route::get('/region/provinsi', [RegionController::class, 'getProvinsi']);
Route::get('/region/kota', [RegionController::class, 'getKota']);
Route::get('/region/kecamatan', [RegionController::class, 'getKecamatan']);
Route::get('/region/kelurahan', [RegionController::class, 'getKelurahan']);

// Log Endpoint
Route::middleware('auth:sanctum')->get('/log', [LogController::class, 'index']);
