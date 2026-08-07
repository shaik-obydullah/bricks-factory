<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('sku') && ! $this->filled('code')) {
            $this->merge(['code' => $this->input('sku')]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $this->route('product'),
            'sku' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'SKU',
        ];
    }
}
