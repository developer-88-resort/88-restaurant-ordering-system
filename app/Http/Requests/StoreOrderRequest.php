<?php

namespace App\Http\Requests;

use App\Models\SpaceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
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
            'order_type' => ['required', 'in:dine_in,takeout'],
            'area_id' => ['required_if:order_type,dine_in', 'nullable', 'exists:areas,id'],
            'space_category_id' => ['required_if:order_type,dine_in', 'nullable', 'exists:space_categories,id'],
            'space_id' => ['nullable', 'exists:spaces,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'distinct', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('order_type') !== 'dine_in') {
                return;
            }

            $category = SpaceCategory::find($this->input('space_category_id'));

            if (! $category) {
                return;
            }

            if ($category->is_free) {
                if ($category->isFull()) {
                    $validator->errors()->add('space_category_id', "\"{$category->name}\" is full.");
                }

                return;
            }

            if (! $this->filled('space_id')) {
                $validator->errors()->add('space_id', 'Please select a space.');
            }
        });
    }
}
