<?php

namespace App\Models;

use App\Events\AuditLogCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (AuditLog $log) {
            broadcast(new AuditLogCreated());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
