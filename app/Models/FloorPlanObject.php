<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloorPlanObject extends Model
{
    use LogsAuditActivity;

    protected $fillable = [
        'area_id',
        'object_type',
        'x',
        'y',
        'rotation',
        'label',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
