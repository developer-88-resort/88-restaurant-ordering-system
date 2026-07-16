<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use App\Enums\SpaceStatus;
use App\Events\SpaceOccupancyChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Space extends Model
{
    use LogsAuditActivity;

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
     * All space IDs transitively connected to this one via shared-table
     * links (including this space itself), found by walking the pivot
     * graph rather than reading only this table's direct links.
     *
     * @return array<int>
     */
    public function groupSpaceIds(): array
    {
        $visited = [$this->id => true];
        $queue = [$this->id];

        while ($queue) {
            $id = array_shift($queue);
            $neighborIds = static::find($id)?->sharedTables()->pluck('spaces.id')->all() ?? [];

            foreach ($neighborIds as $neighborId) {
                if (! isset($visited[$neighborId])) {
                    $visited[$neighborId] = true;
                    $queue[] = $neighborId;
                }
            }
        }

        return array_keys($visited);
    }

    /**
     * Replace this table's shared-table group with the given space IDs.
     *
     * Shared tables behave as one group rather than a parent/child pair: the
     * whole group is linked as a full mesh (every member directly linked to
     * every other member) so the group reads correctly — and the same full
     * membership shows up pre-checked — no matter which table is opened
     * next. Tables dropped from the group are fully detached from every
     * former groupmate, not just from $this.
     *
     * @param  array<int>  $spaceIds
     */
    public function syncSharedTables(array $spaceIds): void
    {
        $requestedPartners = array_values(array_diff(array_unique($spaceIds), [$this->id]));

        $previousGroup = $this->groupSpaceIds();
        $desiredGroup = array_values(array_unique(array_merge([$this->id], $requestedPartners)));

        $droppedIds = array_diff($previousGroup, $desiredGroup);

        foreach ($droppedIds as $droppedId) {
            $dropped = static::find($droppedId);
            if (! $dropped) {
                continue;
            }

            foreach (array_diff($previousGroup, [$droppedId]) as $otherId) {
                $dropped->sharedTables()->detach($otherId);
                static::find($otherId)?->sharedTables()->detach($droppedId);
            }
        }

        foreach ($desiredGroup as $id) {
            $space = $id === $this->id ? $this : static::find($id);
            if (! $space) {
                continue;
            }

            $space->sharedTables()->syncWithoutDetaching(array_diff($desiredGroup, [$id]));
        }
    }

    /**
     * Set this table's status and mirror it onto every linked shared table
     * — combined tables always show the same status as each other,
     * regardless of which one was changed or what the new status is
     * (Occupied, Reserved, Maintenance, or Disabled).
     *
     * Whenever the group goes back to Available — no matter what status it
     * was in before (Occupied, Reserved, Maintenance, or Disabled) — the
     * shared-table links are dissolved automatically so each table is
     * independent again for its next assignment.
     */
    public function setStatusWithSharedTables(SpaceStatus $status): void
    {
        $wasOccupied = $this->status === SpaceStatus::Occupied;
        $wasAvailable = $this->status === SpaceStatus::Available;
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
            broadcast(new SpaceOccupancyChanged(
                $names.' '.($plural ? 'are' : 'is').' now Occupied.',
                $allTables->pluck('id')->all(),
                $status->value,
            ));
        } elseif (! $wasAvailable && $status === SpaceStatus::Available) {
            broadcast(new SpaceOccupancyChanged(
                $names.' '.($plural ? 'have' : 'has').' been vacated.',
                $allTables->pluck('id')->all(),
                $status->value,
            ));
        }

        if ($status === SpaceStatus::Available && $partners->isNotEmpty()) {
            foreach ($allTables as $table) {
                $table->syncSharedTables([]);
            }
        }
    }
}
