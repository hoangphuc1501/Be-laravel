<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\NewsCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSpecificationController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// api danh mục sản phẩm
Route::apiResource('productcategories', ProductCategoryController::class);
Route::prefix('productcategories')->group(function () {
    Route::patch('softDelete/{id}', [ProductCategoryController::class, 'softDelete']); 
    Route::patch('restore/{id}', [ProductCategoryController::class, 'restore']); 
});

// api sản phẩm
Route::apiResource('products', ProductController::class);
Route::prefix('products')->group(function () {
    Route::patch('softDelete/{id}', [ProductController::class, 'softDelete']); 
    Route::patch('restore/{id}', [ProductController::class, 'restore']); 
});

// api thương hiệu
Route::apiResource('brands', BrandController::class);
Route::prefix('brands')->group(function () {
    Route::patch('/softDelete/{id}', [BrandController::class, 'softDelete']);
    Route::patch('/restore/{id}', [BrandController::class, 'restore']);
});

// api thông số kỹ  thuật
Route::apiResource('productspecifications', ProductSpecificationController::class);
Route::prefix('productspecifications')->group(function () {
    Route::patch('/softDelete/{id}', [ProductSpecificationController::class, 'softDelete']);
    Route::patch('/restore/{id}', [ProductSpecificationController::class, 'restore']);
});

// api users
Route::apiResource('users', UserController::class);
Route::prefix('users')->group(function () {
    Route::patch('/softDelete/{id}', [UserController::class, 'softDelete']);
    Route::patch('/restore/{id}', [UserController::class, 'restore']);
});


// api danh mục tin tức
Route::apiResource('newsCategories', NewsCategoryController::class);
Route::prefix('newsCategories')->group(function () {
    Route::patch('softDelete/{id}', [NewsCategoryController::class, 'softDelete']); 
    Route::patch('restore/{id}', [NewsCategoryController::class, 'restore']); 
});