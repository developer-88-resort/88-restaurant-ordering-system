<?php

namespace App\Models;

use App\Concerns\LogsAuditActivity;
use App\Enums\MenuItemAvailability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use LogsAuditActivity, SoftDeletes;

    protected $fillable = [
        'menu_category_id',
        'name',
        'description',
        'price',
        'sku',
        'prep_time_minutes',
        'is_featured',
        'is_best_seller',
        'sort_order',
        'availability_status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'prep_time_minutes' => 'integer',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'sort_order' => 'integer',
            'availability_status' => MenuItemAvailability::class,
        ];
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MenuItemImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class)->orderBy('sort_order');
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'menu_item_modifier_group')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function primaryImage(): ?MenuItemImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function primaryImageUrl(): ?string
    {
        return $this->primaryImage()?->url;
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    /**
     * "₱X.XX" for a plain item, or a range/"From" label once it has
     * variants — the base `price` column stops being the sellable price
     * the moment variants exist (each variant carries its own).
     */
    public function priceRangeLabel(): string
    {
        if (! $this->hasVariants()) {
            return '₱'.number_format($this->price, 2);
        }

        $prices = $this->variants->pluck('price')->map(fn ($price) => (float) $price);
        $min = $prices->min();
        $max = $prices->max();

        if ($min === $max) {
            return '₱'.number_format($min, 2);
        }

        return __('From ₱:min', ['min' => number_format($min, 2)]);
    }

    protected function auditLabel(): string
    {
        return 'Menu Item';
    }
}
