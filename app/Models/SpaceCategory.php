<?php

namespace App\Models;

use App\Enums\SpaceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SpaceCategory extends Model
{
    protected $fillable = [
        'area_id',
        'name',
        'slug',
        'capacity',
        'rental_fee',
        'max_active_occupancy',
        'is_free',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rental_fee' => 'decimal:2',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SpaceCategory $category) {
            $category->slug ??= Str::slug($category->name);
        });
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class, 'category_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SpaceSession::class, 'category_id');
    }

    /**
     * "Shared Capacity" categories (is_free) hide the per-space picker from
     * staff and auto-assign the first available space instead. If the
     * category has individually numbered spaces, occupancy is tracked via
     * their status like any other category; only categories with no spaces
     * at all (a pure capacity pool, e.g. Free Cottage) fall back to the
     * session-counter + max_active_occupancy mechanism.
     */
    public function usesSpacePool(): bool
    {
        return $this->spaces()->exists();
    }

    public function activeOccupancyCount(): int
    {
        if ($this->usesSpacePool()) {
            return $this->spaces()->where('status', SpaceStatus::Occupied)->count();
        }

        return $this->sessions()->where('status', 'active')->count();
    }

    public function capacityCount(): ?int
    {
        return $this->usesSpacePool() ? $this->spaces()->count() : $this->max_active_occupancy;
    }

    public function isFull(): bool
    {
        if ($this->usesSpacePool()) {
            return ! $this->spaces()->where('status', SpaceStatus::Available)->exists();
        }

        return $this->max_active_occupancy !== null
            && $this->activeOccupancyCount() >= $this->max_active_occupancy;
    }
}
