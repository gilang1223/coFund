<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'target_amount',
        'collected_amount',
        'deadline',
        'status',
        'video_url',
        'rejection_note',
        'rejected_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'deadline' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Campaign $campaign) {
            if (empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->title) . '-' . Str::random(6);
            }
            if (is_null($campaign->collected_amount)) {
                $campaign->collected_amount = 0;
            }
        });
    }

    /**
     * Check if the campaign has reached its target.
     */
    public function hasReachedTarget(): bool
    {
        return $this->collected_amount >= $this->target_amount;
    }

    /**
     * Check if the campaign is expired.
     */
    public function isExpired(): bool
    {
        return $this->deadline->isPast();
    }

    /**
     * Get the creator of the campaign.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category of the campaign.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the images for the campaign.
     */
    public function images(): HasMany
    {
        return $this->hasMany(CampaignImage::class);
    }

    /**
     * Get the primary image for the campaign.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(CampaignImage::class)->where('is_primary', true);
    }

    /**
     * Get the tiers for the campaign.
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(CampaignTier::class);
    }

    /**
     * Get the updates for the campaign.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    /**
     * Get the backings for the campaign.
     */
    public function backings(): HasMany
    {
        return $this->hasMany(Backing::class);
    }
}
