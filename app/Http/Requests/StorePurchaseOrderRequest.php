<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,confirmed,in_progress,completed,cancelled,received',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.material_id' => 'nullable|exists:raw_materials,id',
            'items.*.raw_material_id' => 'nullable|exists:raw_materials,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }
}
