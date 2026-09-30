<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mingle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'city_id',
        'venue',
        'address_details',
        'meeting_instructions',
        'starts_at',
        'ends_at',
        'category',
        'interests',
        'tags',
        'visibility',
        'maximum_attendees',
        'allow_guests',
        'is_recurring',
        'is_featured',
        'members_can_create_events',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'                 => 'datetime',
            'ends_at'                   => 'datetime',
            'interests'                 => 'array',
            'tags'                      => 'array',
            'allow_guests'              => 'boolean',
            'is_recurring'              => 'boolean',
            'is_featured'               => 'boolean',
            'members_can_create_events' => 'boolean',
            'maximum_attendees'         => 'integer',
        ];
    }

    /**
     * Mingle owner / creator.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users attending / joining this Mingle.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['status', 'email_reminders', 'reminder_sent_at'])->wherePivot('status', 'accepted')->withTimestamps();
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['status', 'email_reminders', 'reminder_sent_at'])->withTimestamps();
    }

    public function invites(): HasMany
    {
        return $this->hasMany(MingleInvite::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MingleUpdate::class)->latest();
    }

    public function reminderRecipients(): BelongsToMany
    {
        return $this->attendees()->wherePivot('email_reminders', true)->wherePivotNull('reminder_sent_at');
    }

    /**
     * City associated with this Mingle.
     */
    public function cityRecord(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Scope: only events.
     */
    public function scopeEvents($query)
    {
        return $query->where('type', 'event');
    }

    /**
     * Scope: only communities.
     */
    public function scopeCommunities($query)
    {
        return $query->where('type', 'community');
    }

    /**
     * Scope: visible/publicly discoverable Mingles.
     */
    public function scopePubliclyVisible($query)
    {
        return $query->whereIn('visibility', [
            'open',
            'public',
            'request',
        ]);
    }

    /**
     * Check whether this Mingle is an event.
     */
    public function isEvent(): bool
    {
        return $this->type === 'event';
    }

    /**
     * Check whether this Mingle is a community.
     */
    public function isCommunity(): bool
    {
        return $this->type === 'community';
    }

    /**
     * Check whether this Mingle has an attendee limit.
     */
    public function hasAttendeeLimit(): bool
    {
        return ! is_null($this->maximum_attendees);
    }

    /**
     * Check whether the Mingle is full.
     */
    public function isFull(): bool
    {
        if (! $this->hasAttendeeLimit()) {
            return false;
        }

        $attendeeCount          =   $this->getAttribute('attendees_count') ?? $this->attendees()->count();

        return $attendeeCount >= $this->maximum_attendees;
    }

    /**
     * Get the current attendee count.
     */
    public function getAttendeeCountAttribute(): int
    {
        return $this->attendees()->count();
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
