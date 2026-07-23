<?php

namespace App\Http\Requests;

use App\Enums\MenuItemAvailability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'menu_category_id' => ['required', Rule::exists('menu_categories', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // Only truly required when the item has no variants — see
            // withValidator() below. The base price is meaningless once
            // variants (each with their own price) exist.
            'price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:menu_items,sku'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'availability_status' => ['nullable', new Enum(MenuItemAvailability::class)],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'max:2048'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'required_with:variants.*.name', 'numeric', 'min:0'],
            'variants.*.image' => ['nullable', 'image', 'max:2048'],
            'variants.*.remove_image' => ['nullable', 'boolean'],
            'default_variant_index' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasVariants = collect($this->input('variants', []))
                ->contains(fn ($row) => trim((string) ($row['name'] ?? '')) !== '');

            if (! $hasVariants && ! $this->filled('price')) {
                $validator->errors()->add('price', __('Price is required unless you add at least one variant below.'));
            }
        });
    }
}
