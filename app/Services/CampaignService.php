<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\CampaignTier;
use App\Models\CampaignUpdate;
use Illuminate\Support\Str;

class CampaignService extends BaseService
{
    /**
     * Get all campaigns with pagination.
     */
    public function getAll(array $filters = [], int $perPage = 12)
    {
        $query = Campaign::with(['category', 'creator', 'primaryImage']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
     */
    public function approve(int $id): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->status !== 'review') {
            return null;
        }

        $campaign->update(['status' => 'active']);

        return $campaign->fresh();
    }

    /**
     * Reject campaign (admin only).
     */
    public function reject(int $id): ?Campaign
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->status !== 'review') {
            return null;
        }

        $campaign->update(['status' => 'draft']);

        return $campaign->fresh();
    }

    /**
     * Delete a campaign.
     */
    public function delete(int $id): bool
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return false;
        }

        return $campaign->delete();
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
