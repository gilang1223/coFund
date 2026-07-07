<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreatorRequestRequest;
use App\Http\Requests\CreatorRequestRejectRequest;
use App\Services\CreatorRequestService;

class CreatorRequestController extends ApiController
{
    protected CreatorRequestService $creatorRequestService;

    public function __construct(CreatorRequestService $creatorRequestService)
    {
        $this->creatorRequestService = $creatorRequestService;
    }

    /**
     * Backer mengajukan request untuk menjadi creator.
     */
    public function store(CreatorRequestRequest $request)
    {
        $result = $this->creatorRequestService->request(
            auth()->user(),
            $request->validated()['reason']
        );

        if (!$result['success']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendCreated('Request creator berhasil diajukan.', $result['data']);
    }

    /**
     * Backer melihat riwayat request miliknya.
     */
    public function myRequests()
    {
        $requests = $this->creatorRequestService->getByUser(auth()->user());

        return $this->sendPaginatedResponse(
            'Riwayat request creator berhasil diambil.',
            $requests->items(),
            [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
            ]
        );
    }

    /**
     * Admin melihat semua creator requests.
     */
    public function index()
    {
        $filters = request()->only(['status', 'per_page']);
        $requests = $this->creatorRequestService->getAll($filters);

        return $this->sendPaginatedResponse(
            'Daftar creator request berhasil diambil.',
            $requests->items(),
            [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
            ]
        );
    }

    /**
     * Admin menyetujui creator request.
     */
    public function approve(int $id)
    {
        $result = $this->creatorRequestService->approve($id, auth()->user());

        if (!$result['success']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendResponse('Request creator berhasil disetujui. User sekarang menjadi creator.', $result['data']);
    }

    /**
     * Admin menolak creator request dengan catatan opsional.
     */
    public function reject(int $id, CreatorRequestRejectRequest $request)
    {
        $result = $this->creatorRequestService->reject(
            $id,
            auth()->user(),
            $request->validated()['admin_note'] ?? null
        );

        if (!$result['success']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendResponse('Request creator berhasil ditolak.', $result['data']);
    }
}
