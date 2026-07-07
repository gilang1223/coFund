<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\CreatorRequest;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
        'is_suspended',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'is_suspended' => 'boolean',
    ];

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a creator.
     */
    public function isCreator(): bool
    {
        return $this->role === 'creator';
    }

    /**
     * Check if the user is a backer.
     */
    public function isBacker(): bool
    {
        return $this->role === 'backer';
    }

    /**
     * Add balance to user's account.
     */
    public function addBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    /**
     * Deduct balance from user's account.
     */
    public function deductBalance(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }

    /**
     * Get the campaigns created by the user.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'user_id');
    }

    /**
     * Get the backings made by the user.
     */
    public function backings(): HasMany
    {
        return $this->hasMany(Backing::class, 'user_id');
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    /**
     * Get the creator requests made by the user.
     */
    public function creatorRequests(): HasMany
    {
        return $this->hasMany(CreatorRequest::class, 'user_id');
    }

    /**
     * Get the withdrawals made by the user.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'user_id');
    }

    /**
     * Check if the user has a pending creator request.
     */
    public function hasPendingCreatorRequest(): bool
    {
        return $this->creatorRequests()
            ->where('status', CreatorRequest::STATUS_PENDING)
            ->exists();
    }

    /**
     * Send the email verification notification.
     * Override the default MustVerifyEmail notification to use our custom one.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
