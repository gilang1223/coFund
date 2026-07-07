<?php

namespace App\Http\Controllers\Api;

use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends ApiController
{
    protected WithdrawalService $withdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    /**
     * Create a new withdrawal — langsung diproses otomatis.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // Check balance
        if ($user->balance < $request->amount) {
            return $this->sendError('Saldo tidak mencukupi.', 400);
        }

        $withdrawal = $this->withdrawalService->create($user->id, $request->all());

        if (!$withdrawal) {
            return $this->sendError('Penarikan gagal diproses. Minimal penarikan Rp 50.000.', 400);
        }

        return $this->sendCreated('Penarikan berhasil! Dana dikirim ke rekening Anda.', $withdrawal);
    }

    /**
     * Get withdrawals for the authenticated user.
     */
    public function index()
    {
        $withdrawals = $this->withdrawalService->getByUser(auth()->id());

        return $this->sendResponse('Riwayat penarikan berhasil diambil.', $withdrawals);
    }

    /**
     * Admin: get all withdrawals (riwayat penarikan).
     */
    public function adminIndex()
    {
        $filters = request()->only(['status', 'per_page']);
        $withdrawals = $this->withdrawalService->getAll($filters);

        return $this->sendPaginatedResponse(
            'Daftar penarikan berhasil diambil.',
            $withdrawals->items(),
            [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ]
        );
    }
}
