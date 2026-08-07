<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:production_orders,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'machine_id' => 'nullable|exists:machines,id',
            'operator_id' => 'nullable|exists:users,id',
        ];
    }
}
