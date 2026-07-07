<?php

namespace App\Services;

use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WithdrawalService extends BaseService
{
    /**
     * Create a new withdrawal — langsung diproses (auto-approve).
     */
    public function create(int $userId, array $data): ?Withdrawal
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $amount = (float) $data['amount'];

        // Minimum withdrawal
        if ($amount < 50000) {
            return null;
        }

        // Check sufficient balance
        if ($user->balance < $amount) {
            return null;
        }

        return DB::transaction(function () use ($user, $amount, $data) {
            // Deduct from balance
            $user->deductBalance($amount);

            // Calculate fee (1% admin fee for withdrawal)
            $fee = round($amount * 0.01, 2);
            $netAmount = $amount - $fee;

            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'bank_name' => $data['bank_name'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_account_name' => $data['bank_account_name'],
                'status' => Withdrawal::STATUS_SUCCESS,
                'processed_at' => now(),
            ]);

            // Create success transaction
            Transaction::create([
                'user_id' => $user->id,
                'backing_id' => null,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => 'success',
                'reference' => 'WITHDRAW-' . $withdrawal->id . '-' . strtoupper(uniqid()),
            ]);

            // Notify user + send email
            \App\Jobs\SendNotificationJob::dispatch(
                $withdrawal->user_id,
                'withdrawal',
                'Penarikan Berhasil 💰',
                "Penarikan dana sebesar Rp " . number_format($netAmount, 0, ',', '.') . " ke {$data['bank_account_name']} ({$data['bank_name']}) No. {$data['bank_account_number']} berhasil diproses.",
                ['withdrawal_id' => $withdrawal->id, 'amount' => $withdrawal->amount],
                true, // send email
            );

            return $withdrawal->load('user');
        });
    }

    /**
     * Get withdrawals by user.
     */
    public function getByUser(int $userId)
    {
        return Withdrawal::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all withdrawals (admin).
     */
    public function getAll(array $filters = [])
    {
        $query = Withdrawal::with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }
}
