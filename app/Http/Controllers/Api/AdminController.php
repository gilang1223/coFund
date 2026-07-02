<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Backing;
use App\Models\Transaction;

class AdminController extends ApiController
{
    /**
     * Get admin dashboard overview stats.
     */
    public function overview()
    {
        $totalUsers = User::count();
        $totalCreators = User::where('role', 'creator')->count();
        $totalBackers = User::where('role', 'backer')->count();
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $pendingReviews = Campaign::where('status', 'review')->count();
        $successCampaigns = Campaign::where('status', 'success')->count();
        $failedCampaigns = Campaign::where('status', 'failed')->count();
        $totalTransactions = Transaction::count();
        $totalBackings = Backing::count();
        $totalCollected = Campaign::sum('collected_amount');
        $totalPlatformFees = Transaction::where('type', 'platform_fee')->sum('amount');

        return $this->sendResponse('Admin overview retrieved successfully', [
            'users' => [
                'total' => $totalUsers,
                'creators' => $totalCreators,
                'backers' => $totalBackers,
            ],
            'campaigns' => [
                'total' => $totalCampaigns,
                'active' => $activeCampaigns,
                'pending_review' => $pendingReviews,
                'success' => $successCampaigns,
                'failed' => $failedCampaigns,
            ],
            'financials' => [
                'total_collected' => $totalCollected,
                'total_platform_fees' => $totalPlatformFees,
                'total_transactions' => $totalTransactions,
                'total_backings' => $totalBackings,
            ],
        ]);
    }

    /**
     * Get all users with pagination.
     */
    public function users()
    {
        $users = User::select(['id', 'name', 'email', 'role', 'balance', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(request()->get('per_page', 20));

        return $this->sendPaginatedResponse(
            'Users retrieved successfully',
            $users->items(),
            [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        );
    }

    /**
     * Get campaigns pending review.
     */
    public function pendingReviews()
    {
        $campaigns = Campaign::with(['creator', 'category'])
            ->where('status', 'review')
            ->orderBy('created_at', 'asc')
            ->paginate(request()->get('per_page', 20));

        return $this->sendPaginatedResponse(
            'Pending reviews retrieved successfully',
            $campaigns->items(),
            [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
            ]
        );
    }

    /**
     * Get all campaigns (admin view).
     */
    public function allCampaigns()
    {
        $campaigns = Campaign::with(['creator', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(request()->get('per_page', 20));

        return $this->sendPaginatedResponse(
            'Campaigns retrieved successfully',
            $campaigns->items(),
            [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
            ]
        );
    }
}
