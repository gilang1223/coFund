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

        return $this->sendResponse('Campaign retrieved successfully', $campaign);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(CampaignRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $campaign = $this->campaignService->create($data);

        return $this->sendCreated('Campaign created successfully', $campaign);
    }

    /**
     * Update the specified campaign.
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
     */
    public function reject(int $id)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can reject campaigns');
        }

        $campaign = $this->campaignService->reject($id);

        if (!$campaign) {
            return $this->sendError('Campaign cannot be rejected', 400);
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
     * Remove the specified campaign.
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

        $this->campaignService->delete($id);

        return $this->sendResponse('Campaign deleted successfully');
    }
}
