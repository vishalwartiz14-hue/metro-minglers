<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'image_path', 'tagline', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'latitude' => 'float', 'longitude' => 'float']; }
    public function users() { return $this->hasMany(User::class); }
    public function mingles() { return $this->hasMany(Mingle::class); }

    public function getLandingImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->exists($this->image_path)
                ? Storage::disk('public')->url($this->image_path)
                : asset(ltrim($this->image_path, '/'));
        }

        if ($this->image_url) {
            return filter_var($this->image_url, FILTER_VALIDATE_URL)
                ? $this->image_url
                : asset(ltrim($this->image_url, '/'));
        }

        return asset('images/metro-landing.png');
    }
}
