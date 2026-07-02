<?php

namespace App\Http\Controllers\Api;

use App\Services\TransactionService;

class TransactionController extends ApiController
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get transactions for the authenticated user.
     */
    public function index()
    {
        $transactions = $this->transactionService->getByUser(auth()->id());

        return $this->sendResponse('Transactions retrieved successfully', $transactions);
    }

    /**
     * Get a single transaction by reference.
     */
    public function show(string $reference)
    {
        $transaction = $this->transactionService->getByReference($reference);

        if (!$transaction) {
            return $this->sendNotFound('Transaction not found');
        }

        // Only owner or admin can view
        if ($transaction->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return $this->sendForbidden('You are not authorized to view this transaction');
        }

        return $this->sendResponse('Transaction retrieved successfully', $transaction);
    }

    /**
     * Process disbursement for a successful campaign (admin only).
     */
    public function processDisbursement(int $campaignId)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can process disbursements');
        }

        $result = $this->transactionService->processDisbursement($campaignId);

        if (!$result) {
            return $this->sendError('Disbursement cannot be processed', 400);
        }

        return $this->sendResponse('Disbursement processed successfully', $result);
    }

    /**
     * Process refunds for a failed campaign (admin only).
     */
    public function processRefunds(int $campaignId)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can process refunds');
        }

        $result = $this->transactionService->processRefunds($campaignId);

        if (!$result) {
            return $this->sendError('Refunds cannot be processed', 400);
        }

        return $this->sendResponse('Refunds processed successfully');
    }

    /**
     * Settle an expired campaign — auto-determine disbursement or refund.
     * Campaign must be in 'active' status and past deadline.
     */
    public function settleCampaign(int $campaignId)
    {
        if (!auth()->user()?->isAdmin()) {
            return $this->sendForbidden('Only admin can settle campaigns');
        }

        $result = $this->transactionService->settleCampaign($campaignId);

        if (!$result) {
            return $this->sendError('Campaign cannot be settled. It may not be expired or not in active status.', 400);
        }

        $action = $result['action'] ?? 'unknown';
        $message = $action === 'disbursement'
            ? 'Campaign settled successfully. Funds disbursed to creator.'
            : 'Campaign settled successfully. Refunds processed for all backers.';

        return $this->sendResponse($message, $result);
    }
}
