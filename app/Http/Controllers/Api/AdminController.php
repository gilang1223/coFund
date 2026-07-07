<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Backing;
use App\Models\Transaction;
use App\Jobs\RefundBackersJob;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;

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
        $draftCampaigns = Campaign::where('status', 'draft')->count();
        // 'rejected' = campaigns yang pernah ditolak (punya rejected_at)
        $rejectedCampaigns = Campaign::whereNotNull('rejected_at')->count();
        $totalTransactions = Transaction::count();
        $totalBackings = Backing::count();
        $totalCollected = Campaign::sum('collected_amount');
        // Platform fee: dari transaksi platform_fee aktual yang sudah sukses
        $totalPlatformFees = Transaction::where('type', 'platform_fee')
            ->where('status', 'success')
            ->sum('amount');

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
                'draft' => $draftCampaigns,
                'rejected' => $rejectedCampaigns,
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
 * Get monthly campaign creation chart data (last 12 months).
 */
public function monthlyChart()
{
    $months = [];
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $yearMonth = $date->format('Y-m');
        $label = $date->locale('id')->isoFormat('MMM Y');

        $count = Campaign::whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->count();

        $months[] = [
            'label' => $label,
            'year_month' => $yearMonth,
            'total' => $count,
        ];
    }

    return $this->sendResponse('Monthly chart retrieved successfully', $months);
}

/**
 * Force-fail an active campaign (penanganan kasus khusus oleh admin).
 */
public function forceFailCampaign(int $id)
{
    $campaign = Campaign::find($id);

    if (!$campaign) {
        return $this->sendNotFound('Campaign not found');
    }

    if ($campaign->status !== 'active') {
        return $this->sendError('Hanya campaign dengan status active yang bisa di-force fail.', 400);
    }

    // Dispatch refund job — akan handle mark sebagai failed + refund
    RefundBackersJob::dispatch($campaign->id);

    // Notify creator
    SendNotificationJob::dispatch(
        $campaign->user_id,
        'campaign_force_failed',
        'Kampanye Dihentikan 🛑',
        "Kampanye \"{$campaign->title}\" telah dihentikan oleh admin. Semua dana backer akan dikembalikan.",
        ['campaign_id' => $campaign->id, 'action' => 'force_fail'],
        true,
    );

    return $this->sendResponse('Campaign sedang diproses untuk force-fail. Refund akan dikirim ke seluruh backer.');
}

    /**
     * Get all users with pagination and search.
     */
    public function users()
    {
        $query = User::select(['id', 'name', 'email', 'role', 'balance', 'is_suspended', 'created_at'])
            ->where('role', '!=', 'admin');

        // Search by name or email
        $search = request()->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
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
     * Get campaigns pending review (hanya status 'review').
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

    /**
     * Suspend a user account.
     */
    public function suspendUser(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendNotFound('User not found');
        }

        if ($user->isAdmin()) {
            return $this->sendError('Cannot suspend admin accounts', 403);
        }

        $user->update(['is_suspended' => true]);

        // Notify the user + send email
        SendNotificationJob::dispatch(
            $user->id,
            'account',
            'Akun Disuspend',
            'Akun Anda telah disuspend oleh admin. Hubungi admin untuk informasi lebih lanjut.',
            ['action' => 'suspended'],
            true, // send email
        );

        return $this->sendResponse('User suspended successfully', $user);
    }

    /**
     * Reactivate a suspended user account.
     */
    public function reactivateUser(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendNotFound('User not found');
        }

        $user->update(['is_suspended' => false]);

        // Notify the user + send email
        SendNotificationJob::dispatch(
            $user->id,
            'account',
            'Akun Diaktifkan Kembali',
            'Akun Anda telah diaktifkan kembali oleh admin.',
            ['action' => 'reactivated'],
            true, // send email
        );

        return $this->sendResponse('User reactivated successfully', $user);
    }

    /**
     * Get transaction/donation history for a specific user.
     */
    public function userTransactions(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendNotFound('User not found');
        }

        $transactions = Transaction::with(['backing.campaign'])
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(request()->get('per_page', 20));

        return $this->sendPaginatedResponse(
            'User transactions retrieved successfully',
            $transactions->items(),
            [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        );
    }
}
