<?php

namespace App\Http\Requests;

use App\Enums\DiscountEligibilityMethod;
use App\Enums\DiscountType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinalizeOrderPaymentRequest extends FormRequest
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
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_reference' => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:100'],
            'amount_received' => ['required', 'numeric', 'min:0'],

            'discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'discount_qualified_name' => ['required_if:discount_type,senior_citizen,pwd', 'nullable', 'string', 'max:255'],
            'discount_id_number' => ['required_if:discount_type,senior_citizen,pwd', 'nullable', 'string', 'max:100'],
            'discount_promo_percent' => ['required_if:discount_type,promo', 'nullable', 'numeric', 'min:0', 'max:100'],
            'discount_eligibility_method' => ['required_with:discount_type', 'nullable', Rule::enum(DiscountEligibilityMethod::class)],
            'discount_item_ids' => ['required_if:discount_eligibility_method,item_based', 'nullable', 'array', 'min:1'],
            'discount_item_ids.*' => ['integer'],
            'discount_eligible_amount' => ['required_if:discount_eligibility_method,amount_based', 'nullable', 'numeric', 'min:0'],
            'discount_qualified_diners' => ['nullable', 'integer', 'min:1'],
            'discount_total_diners' => ['nullable', 'integer', 'min:1', 'gte:discount_qualified_diners'],
            'discount_notes' => ['nullable', 'string', 'max:500'],

            'buyer_name' => ['nullable', 'string', 'max:255'],
            'buyer_tin' => ['nullable', 'string', 'max:50'],
            'buyer_address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
