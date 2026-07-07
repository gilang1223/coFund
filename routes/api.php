<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\BackingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CreatorRequestController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\SupportMessageController;
use App\Http\Controllers\Api\WithdrawalController;

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

// Password Reset (public)
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('reset-password', [PasswordResetController::class, 'reset']);

// Email Verification (signed route — accessed via email link, not API)
// The verify route is registered as a named route in routes/web.php for SPA redirect

// Authenticated routes (tanpa 'verified' — termasuk verifikasi email & logout)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::put('user/profile', [AuthController::class, 'updateProfile']);

    // Email Verification (harus bisa diakses tanpa verified middleware)
    Route::post('email/verification-notification', [VerificationController::class, 'sendVerificationEmail']);
    Route::get('email/verification-status', [VerificationController::class, 'status']);

    // -------------------------------------------------------------------
    // VERIFIED ROUTES — membutuhkan email terverifikasi (Rule 5)
    // -------------------------------------------------------------------
    Route::middleware('verified')->group(function () {

        // Top-up balance (diblokir untuk suspended user)
        Route::post('user/top-up', [AuthController::class, 'topUp'])->middleware('suspended');

        // Delete account
        Route::delete('user/delete-account', [AuthController::class, 'deleteAccount']);

        // Withdrawals
        Route::post('withdrawals', [WithdrawalController::class, 'store'])->middleware('suspended');
        Route::get('withdrawals', [WithdrawalController::class, 'index']);

        // Notifications

    // Notifications (semua role authenticated)
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // Transactions (semua role authenticated, detail dibatasi di controller)
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::get('transactions/{reference}', [TransactionController::class, 'show']);

        // -------------------------------------------------------------------
        // BACKER ROUTES — tidak boleh admin
        // -------------------------------------------------------------------
        Route::middleware('not_admin')->group(function () {

            // Creator Request: backer mengajukan request jadi creator (diblokir untuk suspended user)
            Route::post('creator-requests', [CreatorRequestController::class, 'store'])->middleware('suspended');
            Route::get('creator-requests/my', [CreatorRequestController::class, 'myRequests']);

            // Backing / Donasi (admin tidak boleh, diblokir untuk suspended user)
            Route::post('backings', [BackingController::class, 'store'])->middleware('suspended');
            Route::get('my-backings', [BackingController::class, 'myBackings']);
            Route::get('campaigns/{campaignId}/backings', [BackingController::class, 'campaignBackings']);
        });

        // -------------------------------------------------------------------
        // CREATOR ROUTES — hanya creator yang boleh
        // -------------------------------------------------------------------
        Route::middleware('creator')->group(function () {
            // Campaign CRUD (diblokir untuk suspended user)
            Route::post('campaigns', [CampaignController::class, 'store'])->middleware('suspended');
            Route::put('campaigns/{id}', [CampaignController::class, 'update'])->middleware('suspended');
            Route::delete('campaigns/{id}', [CampaignController::class, 'destroy'])->middleware('suspended');
            Route::post('campaigns/{id}/submit-review', [CampaignController::class, 'submitForReview'])->middleware('suspended');
            Route::get('my-campaigns', [CampaignController::class, 'myCampaigns']);
            Route::get('dashboard-stats', [CampaignController::class, 'dashboardStats']);
            Route::post('campaigns/{id}/updates', [CampaignController::class, 'addUpdate'])->middleware('suspended');
        });

        // -------------------------------------------------------------------
        // ADMIN ROUTES — hanya admin yang boleh
        // -------------------------------------------------------------------
        Route::middleware('admin')->group(function () {

            // Categories management
            Route::post('categories', [CategoryController::class, 'store']);
            Route::put('categories/{id}', [CategoryController::class, 'update']);
            Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

            // Campaign moderation
            Route::post('campaigns/{id}/approve', [CampaignController::class, 'approve']);
            Route::post('campaigns/{id}/reject', [CampaignController::class, 'reject']);

            // Creator request management
            Route::get('admin/creator-requests', [CreatorRequestController::class, 'index']);
            Route::post('admin/creator-requests/{id}/approve', [CreatorRequestController::class, 'approve']);
            Route::post('admin/creator-requests/{id}/reject', [CreatorRequestController::class, 'reject']);

            // Dashboard & Users
            Route::get('admin/overview', [AdminController::class, 'overview']);
            Route::get('admin/users', [AdminController::class, 'users']);
            Route::get('admin/pending-reviews', [AdminController::class, 'pendingReviews']);
            Route::get('admin/campaigns', [AdminController::class, 'allCampaigns']);
            Route::get('admin/monthly-chart', [AdminController::class, 'monthlyChart']);

            // User Management (suspend/reactivate/transactions)
            Route::post('admin/users/{id}/suspend', [AdminController::class, 'suspendUser']);
            Route::post('admin/users/{id}/reactivate', [AdminController::class, 'reactivateUser']);
            Route::get('admin/users/{id}/transactions', [AdminController::class, 'userTransactions']);

            // Force-fail campaign (Admin khusus — penanganan khusus)
            Route::post('admin/campaigns/{id}/force-fail', [AdminController::class, 'forceFailCampaign']);

            // Escrow & Settlements
            Route::post('campaigns/{campaignId}/disburse', [TransactionController::class, 'processDisbursement']);
            Route::post('campaigns/{campaignId}/refund-all', [TransactionController::class, 'processRefunds']);
            Route::post('campaigns/{campaignId}/settle', [TransactionController::class, 'settleCampaign']);

            // Withdrawals history (riwayat penarikan untuk admin)
            Route::get('admin/withdrawals', [WithdrawalController::class, 'adminIndex']);
        });
    });
});

// Support Messages — bisa diakses oleh semua user termasuk suspended (tanpa middleware 'suspended')
Route::middleware('auth:sanctum')->group(function () {
    Route::get('support-messages', [SupportMessageController::class, 'index']);
    Route::post('support-messages', [SupportMessageController::class, 'store']);
});

// Admin Support Messages
Route::middleware(['auth:sanctum', 'verified', 'admin'])->group(function () {
    Route::get('admin/support-conversations', [SupportMessageController::class, 'adminConversations']);
    Route::get('admin/support-conversations/{userId}', [SupportMessageController::class, 'adminConversation']);
    Route::post('admin/support-conversations/{userId}/reply', [SupportMessageController::class, 'adminReply']);
});
