<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\Backing;
use App\Models\CampaignTier;
use App\Models\CampaignUpdate;
use Illuminate\Support\Str;

class CampaignService extends BaseService
{
    /**
     * Extract YouTube video ID from various YouTube URL formats.
     */
    public function extractYouTubeId(?string $url): ?string
    {
        if (!$url) return null;

        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Get YouTube thumbnail URL from video ID.
     */
    public function getYouTubeThumbnailUrl(?string $videoUrl): ?string
    {
        $videoId = $this->extractYouTubeId($videoUrl);
        if (!$videoId) return null;

        // Try maxresdefault first, fallback to hqdefault
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }

    /**
     * Get all campaigns with pagination.
     * By default only shows 'active' campaigns to public.
     * If status filter or user_id filter is provided, shows accordingly.
     */
    public function getAll(array $filters = [], int $perPage = 12)
    {
        $query = Campaign::with(['category', 'creator', 'primaryImage']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            // Allow status filter for admin/creator views
            $query->where('status', $filters['status']);
        } elseif (empty($filters['user_id'])) {
            // Public listing: ONLY show 'active' campaigns (removed success/failed from public)
            $query->where('status', 'active');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a campaign by ID.
     */
    public function getById(int $id)
    {
        return Campaign::with([
            'category',
            'creator',
            'images',
            'tiers',
            'updates',
        ])->find($id);
    }

    /**
     * Create a new campaign.
     */
    public function create(array $data): Campaign
    {
        $campaign = Campaign::create([
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . Str::random(6),
            'description' => $data['description'],
            'target_amount' => $data['target_amount'],
            'deadline' => $data['deadline'],
            'status' => 'draft',
            'video_url' => $data['video_url'] ?? null,
        ]);

        // Create images if provided
        if (!empty($data['images'])) {
            foreach ($data['images'] as $index => $imageUrl) {
                CampaignImage::create([
                    'campaign_id' => $campaign->id,
                    'url' => $imageUrl,
                    'is_primary' => $index === 0,
                ]);
            }
        } elseif (!empty($data['video_url'])) {
            // Auto-generate YouTube thumbnail as primary image
            $thumbnailUrl = $this->getYouTubeThumbnailUrl($data['video_url']);
            if ($thumbnailUrl) {
                CampaignImage::create([
                    'campaign_id' => $campaign->id,
                    'url' => $thumbnailUrl,
                    'is_primary' => true,
                ]);
            }
        }

        // Create tiers if provided
        if (!empty($data['tiers'])) {
            foreach ($data['tiers'] as $tierData) {
                CampaignTier::create([
                    'campaign_id' => $campaign->id,
                    'name' => $tierData['name'],
                    'min_amount' => $tierData['min_amount'],
                    'quota' => $tierData['quota'],
                    'remaining_quota' => $tierData['quota'],
                    'reward_description' => $tierData['reward_description'] ?? null,
                ]);
            }
        }

        return $campaign->load(['category', 'creator', 'images', 'tiers']);
    }

    /**
     * Update a campaign.
     */
    public function update(int $id, array $data): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return null;
        }

        $updateData = [];

        if (isset($data['category_id'])) {
            $updateData['category_id'] = $data['category_id'];
        }
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
            $updateData['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['target_amount'])) {
            $updateData['target_amount'] = $data['target_amount'];
        }
        if (isset($data['deadline'])) {
            $updateData['deadline'] = $data['deadline'];
        }
        if (isset($data['video_url'])) {
            $updateData['video_url'] = $data['video_url'];

            // Auto-generate YouTube thumbnail if primary image doesn't exist
            if (!$campaign->primaryImage) {
                $thumbnailUrl = $this->getYouTubeThumbnailUrl($data['video_url']);
                if ($thumbnailUrl) {
                    CampaignImage::create([
                        'campaign_id' => $campaign->id,
                        'url' => $thumbnailUrl,
                        'is_primary' => true,
                    ]);
                }
            }
        }

        // Sync tiers if provided
        if (isset($data['tiers'])) {
            $submittedIds = collect($data['tiers'])->pluck('id')->filter()->values()->toArray();

            // Delete tiers removed from the form
            $campaign->tiers()->whereNotIn('id', $submittedIds)->delete();

            foreach ($data['tiers'] as $tierData) {
                if (!empty($tierData['id'])) {
                    // Update existing tier
                    $tier = CampaignTier::find($tierData['id']);
                    if ($tier && $tier->campaign_id === $campaign->id) {
                        $tier->update([
                            'name' => $tierData['name'],
                            'min_amount' => $tierData['min_amount'],
                            'quota' => $tierData['quota'],
                            'remaining_quota' => $tierData['quota'],
                            'reward_description' => $tierData['reward_description'] ?? null,
                        ]);
                    }
                } else {
                    // Create new tier
                    CampaignTier::create([
                        'campaign_id' => $campaign->id,
                        'name' => $tierData['name'],
                        'min_amount' => $tierData['min_amount'],
                        'quota' => $tierData['quota'],
                        'remaining_quota' => $tierData['quota'],
                        'reward_description' => $tierData['reward_description'] ?? null,
                    ]);
                }
            }
        }

        $campaign->update($updateData);

        return $campaign->fresh()->load(['category', 'creator', 'images', 'tiers']);
    }

    /**
     * Submit campaign for review.
     */
    public function submitForReview(int $id): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->status !== 'draft') {
            return null;
        }

        $campaign->update(['status' => 'review']);

        return $campaign->fresh();
    }

    /**
     * Approve campaign (admin only).
     * Can approve only from 'review' status to 'active'.
     */
    public function approve(int $id): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->status !== 'review') {
            return null;
        }

        $campaign->update(['status' => 'active']);

        // Notify creator + send email
        \App\Jobs\SendNotificationJob::dispatch(
            $campaign->user_id,
            'campaign_status',
            'Kampanye Disetujui! 🎉',
            "Kampanye \"{$campaign->title}\" telah disetujui dan sekarang aktif menerima donasi.",
            ['campaign_id' => $campaign->id, 'status' => 'active'],
            true, // send email
        );

        return $campaign->fresh();
    }

    /**
     * Reject campaign (admin only).
     * Reject from 'review' to 'draft' with rejection note for history.
     */
    public function reject(int $id, ?string $rejectionNote = null): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->status !== 'review') {
            return null;
        }

        $campaign->update([
            'status'         => 'draft',
            'rejection_note' => $rejectionNote,
            'rejected_at'    => now(),
        ]);

        // Notify creator + send email
        $noteText = $rejectionNote ? " Alasan: {$rejectionNote}" : '';
        \App\Jobs\SendNotificationJob::dispatch(
            $campaign->user_id,
            'campaign_status',
            'Kampanye Ditolak',
            "Kampanye \"{$campaign->title}\" ditolak oleh admin.{$noteText} Silakan perbaiki dan ajukan kembali.",
            ['campaign_id' => $campaign->id, 'status' => 'rejected', 'rejection_note' => $rejectionNote],
            true, // send email
        );

        return $campaign->fresh();
    }

    /**
     * Delete a campaign (only allowed for 'draft' status).
     */
    public function delete(int $id): bool
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return false;
        }

        // Rule 9: Kampanye tidak bisa dihapus setelah status bukan draft
        if ($campaign->status !== 'draft') {
            return false;
        }

        return $campaign->delete();
    }

    /**
     * Get dashboard stats for a user.
     */
    public function getUserStats($user): array
    {
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'balance' => $user->balance,
            ],
            'campaigns_count' => Campaign::where('user_id', $user->id)->count(),
            'backings_count' => Backing::where('user_id', $user->id)->count(),
            'total_backed' => Backing::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'active_campaigns' => Campaign::where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
            'total_collected' => Campaign::where('user_id', $user->id)
                ->sum('collected_amount'),
        ];
    }

    /**
     * Add an update to a campaign.
     */
    public function addUpdate(int $campaignId, array $data): ?CampaignUpdate
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return null;
        }

        return CampaignUpdate::create([
            'campaign_id' => $campaignId,
            'title' => $data['title'],
            'content' => $data['content'],
        ]);
    }
}
