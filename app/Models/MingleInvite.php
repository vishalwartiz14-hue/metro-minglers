<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MingleInvite extends Model
{
    protected $fillable = ['mingle_id', 'user_id', 'inviter_id', 'status'];

    public function mingle(): BelongsTo
    {
        return $this->belongsTo(Mingle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }
}
