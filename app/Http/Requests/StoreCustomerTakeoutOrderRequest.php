<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The Welcome (lobby QR) flow's "Order Food" tile — a takeout order with
 * no table/space at all, unlike StoreCustomerOrderRequest which always
 * derives its space from a scanned {space:qr_token}.
 */
class StoreCustomerTakeoutOrderRequest extends FormRequest
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
            'customer_name' => ['nullable', 'string', 'max:255'],
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
