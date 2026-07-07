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
     * Store a newly created backing (pending) and auto-complete it (deduct from balance).
     */
    public function store(BackingRequest $request)
    {
        if (auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Admin tidak diizinkan melakukan backing/donasi.');
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $backing = $this->backingService->create($data);

        if (!$backing) {
            return $this->sendValidationError('Backing gagal. Kampanye mungkin tidak aktif atau validasi gagal.');
        }

        // Auto-complete: deduct balance & increment campaign collected_amount
        $completed = $this->backingService->complete($backing->id);

        if (!$completed) {
            return $this->sendError('Donasi gagal diproses. Saldo tidak mencukupi.', 400);
        }

        return $this->sendCreated('Donasi berhasil!', $completed);
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
