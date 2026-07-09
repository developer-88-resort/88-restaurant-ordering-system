<?php

namespace App\Models;

use App\Enums\SpaceStatus;
use App\Events\SpaceOccupancyChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Space extends Model
{
    protected $fillable = [
        'area_id',
        'category_id',
        'name',
        'code',
        'status',
        'capacity',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => SpaceStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Space $space) {
            $space->qr_token ??= Str::random(24);
            $space->status ??= SpaceStatus::Available;
            $space->code ??= $space->name;
        });
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SpaceCategory::class, 'category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SpaceSession::class);
    }

    /**
     * Tables combined with this one for large groups (symmetric — linking A
     * to B always stores both directions, so this relation reads correctly
     * from either side).
     */
    public function sharedTables(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'space_shared_tables', 'space_id', 'shared_space_id')
            ->withTimestamps();
    }

    /**
     * Replace this table's shared-table links with the given space IDs,
     * keeping both directions of each pair in sync.
     *
     * @param  array<int>  $spaceIds
     */
    public function syncSharedTables(array $spaceIds): void
    {
        $spaceIds = array_diff(array_unique($spaceIds), [$this->id]);

        $current = $this->sharedTables()->pluck('spaces.id')->all();

        foreach (array_diff($current, $spaceIds) as $removedId) {
            $this->sharedTables()->detach($removedId);
            Space::find($removedId)?->sharedTables()->detach($this->id);
        }

        foreach (array_diff($spaceIds, $current) as $addedId) {
            $this->sharedTables()->syncWithoutDetaching($addedId);
            Space::find($addedId)?->sharedTables()->syncWithoutDetaching($this->id);
        }
    }

    /**
     * Set this table's status and mirror it onto every linked shared table
     * — combined tables always show the same status as each other,
     * regardless of which one was changed or what the new status is.
     *
     * When a combined group goes from Occupied back to Available (the group
     * has been vacated), the shared-table links are dissolved automatically
     * so each table is independent again for its next assignment.
     */
    public function setStatusWithSharedTables(SpaceStatus $status): void
    {
        $wasOccupied = $this->status === SpaceStatus::Occupied;
        $partners = $this->sharedTables;

        $this->update(['status' => $status]);

        foreach ($partners as $sharedSpace) {
            if ($sharedSpace->status !== $status) {
                $sharedSpace->update(['status' => $status]);
            }
        }

        $allTables = $partners->isEmpty() ? collect([$this]) : $partners->push($this)->sortBy('sort_order');
        $names = $allTables->pluck('name')->implode(', ');
        $plural = $allTables->count() > 1;

        if (! $wasOccupied && $status === SpaceStatus::Occupied) {
            broadcast(new SpaceOccupancyChanged($names.' '.($plural ? 'are' : 'is').' now Occupied.'));
        } elseif ($wasOccupied && $status === SpaceStatus::Available) {
            broadcast(new SpaceOccupancyChanged($names.' '.($plural ? 'have' : 'has').' been vacated.'));

            if ($partners->isNotEmpty()) {
                foreach ($allTables as $table) {
                    $table->syncSharedTables([]);
                }
            }
        }
    }
}
