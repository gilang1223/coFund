<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatorRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:20|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min'      => 'Alasan minimal 20 karakter.',
            'reason.max'      => 'Alasan maksimal 1000 karakter.',
        ];
    }
}
