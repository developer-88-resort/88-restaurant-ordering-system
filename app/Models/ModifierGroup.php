<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use App\Enums\ModifierSelectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use LogsAuditActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'selection_type',
        'is_required',
        'min_select',
        'max_select',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'selection_type' => ModifierSelectionType::class,
            'is_required' => 'boolean',
            'min_select' => 'integer',
            'max_select' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ModifierOption::class)->orderBy('sort_order');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_modifier_group')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    protected function auditLabel(): string
    {
        return 'Modifier Group';
    }
}
