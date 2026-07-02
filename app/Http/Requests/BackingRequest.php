<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'campaign_id' => 'required|exists:campaigns,id',
            'tier_id' => 'nullable|exists:campaign_tiers,id',
            'amount' => 'required|numeric|min:10000',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'campaign_id.required' => 'Kampanye wajib dipilih.',
            'campaign_id.exists' => 'Kampanye tidak ditemukan.',
            'amount.required' => 'Jumlah donasi wajib diisi.',
            'amount.min' => 'Minimal donasi adalah Rp10.000.',
        ];
    }
}
