<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerificationController;

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

// Email Verification — signed URL route (must be before the SPA catch-all)
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

// SPA catch-all: serve the Vue app for all other routes
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
