<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkTablesRequest extends FormRequest
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
            'prefix' => ['required', 'string', 'max:30'],
            'start' => ['required', 'integer', 'min:1', 'max:9999'],
            'count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
