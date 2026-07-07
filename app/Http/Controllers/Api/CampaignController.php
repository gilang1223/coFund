<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CampaignRequest;
use App\Services\CampaignService;

class CampaignController extends ApiController
{
    protected CampaignService $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of campaigns.
     */
    public function index()
    {
        $filters = request()->only(['status', 'category_id', 'search']);
        $campaigns = $this->campaignService->getAll($filters);

        // Hide sensitive creator data from public listing
        $campaigns->getCollection()->each(function ($campaign) {
            if ($campaign->relationLoaded('creator')) {
                $campaign->creator->makeHidden(['email', 'balance', 'is_suspended']);
            }
        });

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
     * Display the specified campaign.
     */
    public function show(int $id)
    {
        $campaign = $this->campaignService->getById($id);

        if (!$campaign) {
            return $this->sendNotFound('Campaign not found');
        }

        $user = auth()->user();

        // Restrict: only active campaigns are publicly viewable.
        // Creator can see their own campaigns in any status.
        // Admin can see all campaigns.
        $isOwner = $user && $user->id === $campaign->user_id;
        $isAdmin = $user && $user->isAdmin();

        if ($campaign->status !== 'active' && !$isOwner && !$isAdmin) {
            return $this->sendNotFound('Campaign not found');
        }

        // Hide sensitive creator data from users who are neither owner nor admin
        if (!$isOwner && !$isAdmin && $campaign->relationLoaded('creator')) {
            $campaign->creator->makeHidden(['email', 'balance', 'is_suspended']);
        }

        // Append backings count
        $campaign->backings_count = $campaign->backings()->count();

        return $this->sendResponse('Campaign retrieved successfully', $campaign);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(CampaignRequest $request)
    {
        if (auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Admin tidak diizinkan membuat campaign.');
        }

        if (!auth()->user()?->isCreator()) {
            return $this->sendForbidden('Hanya creator yang dapat membuat campaign. Ajukan request creator terlebih dahulu.');
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $campaign = $this->campaignService->create($data);

        return $this->sendCreated('Campaign created successfully', $campaign);
    }

    /**
     * Update the specified campaign (only if status is 'draft' or 'rejected' → 'draft').
     */
    public function update(CampaignRequest $request, int $id)
    {
        $campaign = $this->campaignService->getById($id);

        if (!$campaign) {
            return $this->sendNotFound('Campaign not found');
        }

        // Only creator or admin can update
        if ($campaign->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->sendForbidden('You are not authorized to update this campaign');
        }

        // Only draft campaigns can be updated (termasuk rejected → draft)
        if ($campaign->status !== 'draft') {
            return $this->sendError('Hanya kampanye dengan status draft yang dapat diedit. Kirim kampanye ke review setelah selesai mengedit.', 400);
        }

        $campaign = $this->campaignService->update($id, $request->validated());

        return $this->sendResponse('Campaign updated successfully', $campaign);
    }

    /**
     * Submit campaign for review.
     */
    public function submitForReview(int $id)
    {
        $campaign = $this->campaignService->getById($id);

        if (!$campaign) {
            return $this->sendNotFound('Campaign not found');
        }

        if ($campaign->user_id !== auth()->id()) {
            return $this->sendForbidden('You can only submit your own campaigns for review');
        }

        $campaign = $this->campaignService->submitForReview($id);

        if (!$campaign) {
            return $this->sendError('Campaign cannot be submitted for review', 400);
        }

        return $this->sendResponse('Campaign submitted for review successfully', $campaign);
    }

    /**
     * Approve campaign (admin only).
     */
    public function approve(int $id)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can approve campaigns');
        }

        $campaign = $this->campaignService->approve($id);

        if (!$campaign) {
            return $this->sendError('Campaign cannot be approved', 400);
        }

        return $this->sendResponse('Campaign approved successfully', $campaign);
    }

    /**
     * Reject campaign (admin only).
     * Wajib menyertakan catatan alasan penolakan.
     */
    public function reject(int $id)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can reject campaigns');
        }

        request()->validate([
            'rejection_note' => 'required|string|min:10|max:1000',
        ]);

        $campaign = $this->campaignService->reject($id, request()->input('rejection_note'));

        if (!$campaign) {
            return $this->sendError('Campaign cannot be rejected. Only campaigns in review status can be rejected.', 400);
        }

        return $this->sendResponse('Campaign rejected successfully', $campaign);
    }

    /**
     * Get campaigns for the authenticated user.
     */
    public function myCampaigns()
    {
        $filters = request()->only(['status']);
        $filters['user_id'] = auth()->id();
        $campaigns = $this->campaignService->getAll($filters);

        return $this->sendPaginatedResponse(
            'My campaigns retrieved successfully',
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
     * Get dashboard stats for the authenticated user.
     */
    public function dashboardStats()
    {
        $user = auth()->user();
        $stats = $this->campaignService->getUserStats($user);

        return $this->sendResponse('Dashboard stats retrieved successfully', $stats);
    }

    /**
     * Remove the specified campaign (only if status is 'draft').
     */
    public function destroy(int $id)
    {
        $campaign = $this->campaignService->getById($id);

        if (!$campaign) {
            return $this->sendNotFound('Campaign not found');
        }

        // Only creator or admin can delete
        if ($campaign->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->sendForbidden('You are not authorized to delete this campaign');
        }

        // Rule 9: Only draft campaigns can be deleted
        if ($campaign->status !== 'draft') {
            return $this->sendError('Hanya kampanye dengan status draft yang dapat dihapus.', 400);
        }

        $this->campaignService->delete($id);
 
         return $this->sendResponse('Campaign deleted successfully');
     }
 
     /**
      * Add a progress update to the campaign.
      */
     public function addUpdate(int $id)
     {
         $campaign = $this->campaignService->getById($id);
 
         if (!$campaign) {
             return $this->sendNotFound('Campaign not found');
         }
 
         if ($campaign->user_id !== auth()->id()) {
             return $this->sendForbidden('Anda hanya dapat memposting update untuk kampanye milik Anda sendiri.');
         }
 
         if ($campaign->status !== 'active') {
             return $this->sendError('Hanya kampanye dengan status aktif yang dapat ditambahkan update.', 400);
         }
 
         request()->validate([
             'title' => 'required|string|max:100',
             'content' => 'required|string|max:5000',
         ]);
 
         $update = $this->campaignService->addUpdate($id, request()->only(['title', 'content']));
 
         if (!$update) {
             return $this->sendError('Gagal menambahkan update kampanye.', 500);
         }
 
         // Notify all backers (in-app only)
         $backers = \App\Models\Backing::where('campaign_id', $id)
             ->where('status', 'completed')
             ->pluck('user_id')
             ->unique();
 
         foreach ($backers as $backerId) {
             \App\Jobs\SendNotificationJob::dispatch(
                 $backerId,
                 'campaign_update',
                 "Update Baru: {$campaign->title} 📢",
                 "Creator baru saja memposting update: \"{$update->title}\". Silakan cek detail kampanye.",
                 ['campaign_id' => $campaign->id, 'update_id' => $update->id],
                 false, // send email
             );
         }
 
         return $this->sendCreated('Campaign update posted successfully', $update);
     }
 }
