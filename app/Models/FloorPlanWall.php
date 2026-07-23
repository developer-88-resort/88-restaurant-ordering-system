<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloorPlanWall extends Model
{
    use LogsAuditActivity;

    protected $fillable = [
        'area_id',
        'x1',
        'y1',
        'x2',
        'y2',
        'thickness',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
