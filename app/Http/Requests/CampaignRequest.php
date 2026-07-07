<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
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
        $isStore = $this->isMethod('post');

        $rules = [
            'category_id' => ($isStore ? 'required' : 'sometimes') . '|exists:categories,id',
            'title' => ($isStore ? 'required' : 'sometimes') . '|string|max:255',
            'description' => ($isStore ? 'required' : 'sometimes') . '|string',
            'target_amount' => ($isStore ? 'required' : 'sometimes') . '|numeric|min:100000',
            'deadline' => ($isStore ? 'required' : 'sometimes') . '|date|after:' . now()->addDays(7)->format('Y-m-d'),
            'video_url' => ($isStore ? 'required' : 'sometimes') . '|url',
        ];

        if ($isStore) {
            $rules['images'] = 'nullable|array';
            $rules['images.*'] = 'url';
            $rules['tiers'] = 'nullable|array';
            $rules['tiers.*.name'] = 'required_with:tiers|string|max:255';
            $rules['tiers.*.min_amount'] = 'required_with:tiers|numeric|min:10000';
            $rules['tiers.*.quota'] = 'required_with:tiers|integer|min:1';
            $rules['tiers.*.reward_description'] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'title.required' => 'Judul kampanye wajib diisi.',
            'description.required' => 'Deskripsi kampanye wajib diisi.',
            'target_amount.required' => 'Target donasi wajib diisi.',
            'target_amount.min' => 'Minimal target donasi adalah Rp100.000.',
            'deadline.required' => 'Batas waktu kampanye wajib diisi.',
            'deadline.after' => 'Minimal batas waktu adalah H+7 dari sekarang.',
            'video_url.required' => 'URL video YouTube wajib diisi.',
            'video_url.url' => 'Format URL video tidak valid.',
            'tiers.*.name.required_with' => 'Nama tier wajib diisi.',
            'tiers.*.min_amount.required_with' => 'Jumlah minimal tier wajib diisi.',
            'tiers.*.min_amount.min' => 'Minimal donasi tier adalah Rp10.000.',
            'tiers.*.quota.required_with' => 'Kuota tier wajib diisi.',
            'tiers.*.quota.min' => 'Minimal kuota tier adalah 1.',
        ];
    }
}
