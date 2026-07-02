<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BackingRequest;
use App\Services\BackingService;

class BackingController extends ApiController
{
    protected BackingService $backingService;

    public function __construct(BackingService $backingService)
    {
        $this->backingService = $backingService;
    }

    /**
     * Store a newly created backing.
     */
    public function store(BackingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $backing = $this->backingService->create($data);

        if (!$backing) {
            return $this->sendValidationError('Backing failed. Campaign may not be active or validation failed.');
        }

        return $this->sendCreated('Backing created successfully', $backing);
    }

    /**
     * Complete a backing (payment success).
     */
    public function complete(int $id)
    {
        $backing = $this->backingService->complete($id);

        if (!$backing) {
            return $this->sendError('Backing cannot be completed', 400);
        }

        return $this->sendResponse('Backing completed successfully', $backing);
    }

    /**
     * Refund/cancel a backing.
     */
    public function refund(int $id)
    {
        $backing = $this->backingService->refund($id);

        if (!$backing) {
            return $this->sendError('Backing cannot be refunded', 400);
        }

        return $this->sendResponse('Backing refunded successfully', $backing);
    }

    /**
     * Get backings for current user.
     */
    public function myBackings()
    {
        $backings = $this->backingService->getByUser(auth()->id());

        return $this->sendResponse('My backings retrieved successfully', $backings);
    }

    /**
     * Get backings for a campaign.
     */
    public function campaignBackings(int $campaignId)
    {
        $backings = $this->backingService->getByCampaign($campaignId);

        return $this->sendResponse('Campaign backings retrieved successfully', $backings);
    }
}
