<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MingleUpdate extends Model
{
    protected $fillable = ['mingle_id', 'user_id', 'body'];

    public function mingle(): BelongsTo
    {
        return $this->belongsTo(Mingle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
