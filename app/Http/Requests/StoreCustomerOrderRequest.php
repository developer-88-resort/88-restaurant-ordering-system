<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Deliberately narrower than StoreOrderRequest (the staff version): a
 * customer request never carries order_type/area_id/space_category_id/
 * space_id — those are 100% derived server-side from the {space:qr_token}
 * the customer scanned, so an anonymous POST can never redirect its own
 * order to a different table.
 */
class StoreCustomerOrderRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => [
                'required',
                'distinct',
                Rule::exists('menu_items', 'id')->where('is_available', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
