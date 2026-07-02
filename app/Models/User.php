<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
}
