<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignTier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'name',
        'min_amount',
        'quota',
        'remaining_quota',
        'reward_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'quota' => 'integer',
        'remaining_quota' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (CampaignTier $tier) {
            if (is_null($tier->remaining_quota)) {
                $tier->remaining_quota = $tier->quota;
            }
        });
    }

    /**
     * Check if the tier is sold out.
     */
    public function isSoldOut(): bool
    {
        return $this->remaining_quota <= 0;
    }

    /**
     * Get the campaign that owns the tier.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the backings for this tier.
     */
    public function backings(): HasMany
    {
        return $this->hasMany(Backing::class);
    }
}
