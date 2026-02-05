<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,pro_sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'attributes' => 'nullable|array',
            'is_active' => 'boolean',
            'category_id' => 'nullable|exists:categories,cat_id'
        ];
    }
	
	public function messages(): array
    {
		return [
            'name.required' => 'الاسم الكامل مطلوب',
            'price.required' => 'السعر مطلوب',
            'stock.required' => 'الكمية في المخزن مطلوب',
            'sku.unique' => 'كود sku موجود مسبقا',
            'barcode.unique' => 'كود barcode موجود مسبقا',
            'category_id.exists' => 'رقم التصنيف غير موجود',
        ];
    }
}
