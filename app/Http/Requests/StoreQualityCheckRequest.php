<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQualityCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'required|exists:production_batches,id',
            'check_date' => 'required|date',
            'inspector_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:pending,passed,failed',
            'notes' => 'nullable|string',
        ];
    }
}
