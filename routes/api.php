<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\BackingController;
use App\Http\Controllers\Api\TransactionController;

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

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('campaigns', [CampaignController::class, 'index']);
Route::get('campaigns/{id}', [CampaignController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    // Categories (admin only)
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Campaigns
    Route::get('my-campaigns', [CampaignController::class, 'myCampaigns']);
    Route::get('dashboard-stats', [CampaignController::class, 'dashboardStats']);
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::put('campaigns/{id}', [CampaignController::class, 'update']);
    Route::delete('campaigns/{id}', [CampaignController::class, 'destroy']);
    Route::post('campaigns/{id}/submit-review', [CampaignController::class, 'submitForReview']);
    Route::post('campaigns/{id}/approve', [CampaignController::class, 'approve']);
    Route::post('campaigns/{id}/reject', [CampaignController::class, 'reject']);

    // Backings
    Route::post('backings', [BackingController::class, 'store']);
    Route::post('backings/{id}/complete', [BackingController::class, 'complete']);
    Route::post('backings/{id}/refund', [BackingController::class, 'refund']);
    Route::get('my-backings', [BackingController::class, 'myBackings']);
    Route::get('campaigns/{campaignId}/backings', [BackingController::class, 'campaignBackings']);

    // Transactions & Escrow
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{reference}', [TransactionController::class, 'show']);
    Route::post('campaigns/{campaignId}/disburse', [TransactionController::class, 'processDisbursement']);
    Route::post('campaigns/{campaignId}/refund-all', [TransactionController::class, 'processRefunds']);
    Route::post('campaigns/{campaignId}/settle', [TransactionController::class, 'settleCampaign']);
});
