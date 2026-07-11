<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use LogsAuditActivity;

    protected $fillable = [
        'resort_name',
        'address',
        'contact_number',
        'email',
        'opening_time',
        'closing_time',
    ];

    protected function casts(): array
    {
        return [
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['resort_name' => '88 Hot Spring Resort']);
    }

    protected function auditLabel(): string
    {
        return 'Settings';
    }
}
