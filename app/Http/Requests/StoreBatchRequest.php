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
            'order_id' => 'nullable|exists:production_orders,id',
            'production_order_id' => 'required_without:order_id|exists:production_orders,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'shift' => 'nullable|string',
            'machine_id' => 'nullable|exists:machines,id',
            'operator_id' => 'nullable|exists:users,id',
            'quantity' => 'nullable|numeric|min:0',
        ];
    }
}
