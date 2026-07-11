<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use LogsAuditActivity;

    protected $fillable = [
        'menu_category_id',
        'name',
        'price',
        'image_path',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    protected function auditLabel(): string
    {
        return 'Menu Item';
    }
}
