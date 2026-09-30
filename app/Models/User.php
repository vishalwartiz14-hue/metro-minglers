<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'date_of_birth', 'gender', 'city_id', 'zip_code', 'about_me', 'occupation',
        'education', 'interests', 'joining_reasons', 'dating_mode', 'onboarding_completed_at',
        'last_active_at', 'profile_photo_path', 'cover_photo_path', 'deactivated_at',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [    
            'email_verified_at'     =>  'datetime',
            'password'              =>  'hashed',
            'date_of_birth'         =>  'date',
            'interests'             =>  'array',
            'joining_reasons'       =>  'array',
            'dating_mode'           =>  'boolean',
            'onboarding_completed_at' => 'datetime',
            'last_active_at'        =>  'datetime',
            'deactivated_at'        =>  'datetime', 'is_admin' => 'boolean',
        ];
    }

    public function mingles()
    {
        return $this->hasMany(Mingle::class);
    }

    public function joinedMingles(): BelongsToMany
    {
        return $this->belongsToMany(Mingle::class)->withPivot(['status', 'email_reminders', 'reminder_sent_at'])->wherePivot('status', 'accepted')->withTimestamps();
    }

    public function receivedMingleInvites(): HasMany
    {
        return $this->hasMany(MingleInvite::class, 'user_id');
    }

    public function cityRecord()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function interestOptions()
    {
        return $this->belongsToMany(Interest::class)->withTimestamps();
    }

    public function sentConnections(): HasMany
    {
        return $this->hasMany(UserConnection::class, 'sender_id');
    }

    public function receivedConnections(): HasMany
    {
        return $this->hasMany(UserConnection::class, 'recipient_id');
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id')->withTimestamps();
    }

    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocked_id', 'blocker_id')->withTimestamps();
    }

    public function hasBlockedOrBeenBlockedBy(int $userId): bool
    {
        return $this->blockedUsers()->whereKey($userId)->exists()
            || $this->blockedByUsers()->whereKey($userId)->exists();
    }

    public function isConnectedWith(int $userId): bool
    {
        return UserConnection::query()->where('status', UserConnection::ACCEPTED)
            ->where(function ($query) use ($userId): void {
                $query->where(fn ($pair) => $pair->where('sender_id', $this->id)->where('recipient_id', $userId))
                    ->orWhere(fn ($pair) => $pair->where('sender_id', $userId)->where('recipient_id', $this->id));
            })->exists();
    }

    public function isOnline(): bool
    {
        return $this->last_active_at?->greaterThanOrEqualTo(now()->subMinutes(5)) ?? false;
    }
}
