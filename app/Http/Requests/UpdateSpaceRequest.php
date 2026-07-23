<?php

namespace App\Http\Requests;

use App\Enums\SpaceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSpaceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', new Enum(SpaceStatus::class)],
            'shape' => ['required', 'in:rectangle,circle,long_table'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'width' => ['nullable', 'integer', 'min:20', 'max:800'],
            'height' => ['nullable', 'integer', 'min:20', 'max:800'],
            'rotation' => ['nullable', 'integer', 'min:0', 'max:359'],
            'shared_space_ids' => ['nullable', 'array'],
            'shared_space_ids.*' => ['integer', 'exists:spaces,id'],
        ];
    }
}
