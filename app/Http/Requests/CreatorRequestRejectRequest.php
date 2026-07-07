<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatorRequestRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_note.max' => 'Catatan admin maksimal 500 karakter.',
        ];
    }
}
