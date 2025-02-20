<?php

use Illuminate\Support\Facades\Route;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\CloudinaryController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::post('/upload-cloudinary', [CloudinaryController::class, 'upload']);

// Route::get('/upload', function () {
//     return view('upload');
// });