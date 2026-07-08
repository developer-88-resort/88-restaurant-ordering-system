<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTableRequest extends FormRequest
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
            'table_number' => ['required', 'string', 'max:50', 'unique:tables,table_number,'.$this->route('table')->id],
            'status' => ['required', new Enum(TableStatus::class)],
        ];
    }
}
