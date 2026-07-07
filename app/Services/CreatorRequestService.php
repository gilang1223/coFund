<?php

namespace App\Services;

use App\Models\CreatorRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CreatorRequestService extends BaseService
{
    /**
     * Backer mengajukan request untuk menjadi creator.
     * Gagal jika: sudah creator/admin, atau masih ada request pending.
     */
    public function request(User $user, string $reason): array
    {
        if ($user->isCreator()) {
            return ['success' => false, 'message' => 'Anda sudah menjadi creator.'];
        }

        if ($user->isAdmin()) {
            return ['success' => false, 'message' => 'Admin tidak dapat mengajukan request creator.'];
        }

        $hasPending = CreatorRequest::where('user_id', $user->id)
            ->where('status', CreatorRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return ['success' => false, 'message' => 'Anda masih memiliki request yang sedang menunggu review.'];
        }

        $creatorRequest = CreatorRequest::create([
            'user_id' => $user->id,
            'reason'  => $reason,
            'status'  => CreatorRequest::STATUS_PENDING,
        ]);

        return ['success' => true, 'data' => $creatorRequest->load('user')];
    }

    /**
     * Admin menyetujui request. Role user diubah ke 'creator'.
     */
    public function approve(int $requestId, User $admin): array
    {
        $creatorRequest = CreatorRequest::with('user')->find($requestId);

        if (!$creatorRequest) {
            return ['success' => false, 'message' => 'Request tidak ditemukan.'];
        }

        if (!$creatorRequest->isPending()) {
            return ['success' => false, 'message' => 'Request ini sudah diproses sebelumnya.'];
        }

        $creatorRequest->update([
            'status'      => CreatorRequest::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Upgrade role user ke creator
        $creatorRequest->user->update(['role' => 'creator']);

        // Send notification + email (via job)
        \App\Jobs\SendNotificationJob::dispatch(
            $creatorRequest->user_id,
            'creator_request',
            'Selamat! Anda sekarang Creator 🎉',
            'Pengajuan creator Anda telah disetujui oleh admin. Anda sekarang bisa membuat kampanye.',
            ['request_id' => $creatorRequest->id, 'status' => 'approved', 'action_label' => 'Buat Kampanye', 'action_url' => config('app.frontend_url', config('app.url')) . '/campaigns/create'],
            true, // send email
        );

        return ['success' => true, 'data' => $creatorRequest->fresh(['user', 'reviewer'])];
    }

    /**
     * Admin menolak request dengan catatan opsional.
     */
    public function reject(int $requestId, User $admin, ?string $adminNote = null): array
    {
        $creatorRequest = CreatorRequest::with('user')->find($requestId);

        if (!$creatorRequest) {
            return ['success' => false, 'message' => 'Request tidak ditemukan.'];
        }

        if (!$creatorRequest->isPending()) {
            return ['success' => false, 'message' => 'Request ini sudah diproses sebelumnya.'];
        }

        $creatorRequest->update([
            'status'      => CreatorRequest::STATUS_REJECTED,
            'admin_note'  => $adminNote,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Send notification + email (via job)
        $noteText = $adminNote ? " Catatan: {$adminNote}" : '';
        \App\Jobs\SendNotificationJob::dispatch(
            $creatorRequest->user_id,
            'creator_request',
            'Pengajuan Creator Ditolak',
            "Pengajuan creator Anda telah ditolak oleh admin.{$noteText} Silakan ajukan ulang dengan alasan yang lebih baik.",
            ['request_id' => $creatorRequest->id, 'status' => 'rejected', 'admin_note' => $adminNote],
            true, // send email
        );

        return ['success' => true, 'data' => $creatorRequest->fresh(['user', 'reviewer'])];
    }

    /**
     * Admin melihat semua creator requests dengan filter opsional.
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = CreatorRequest::with(['user', 'reviewer'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Backer melihat riwayat request miliknya.
     */
    public function getByUser(User $user): LengthAwarePaginator
    {
        return CreatorRequest::where('user_id', $user->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}
