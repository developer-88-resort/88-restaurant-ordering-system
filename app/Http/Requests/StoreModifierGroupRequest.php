<?php

namespace App\Http\Requests;

use App\Enums\ModifierSelectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreModifierGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', new Enum(ModifierSelectionType::class)],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.name' => ['nullable', 'string', 'max:255'],
            'options.*.price_delta' => ['nullable', 'numeric'],
            'options.*.sku' => ['nullable', 'string', 'max:100'],
        ];
    }
}
