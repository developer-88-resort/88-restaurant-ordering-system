<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'table_number',
        'status',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RestaurantTable $table) {
            $table->qr_token ??= Str::random(24);
            $table->status ??= TableStatus::Available;
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
