<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order;

class UpdateOrderRequest extends FormRequest
{
    private $order_id;
	/**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $orderId = $this->route('order_id');
        $user = auth()->user()->id;
		
		if (auth()->check() && auth()->user()->role == 'admin') {
            $user = 0;
        }
		
		$order = Order::getDataWithDetails(0,$orderId,$user);
        
        if (!$order || empty($order)) {
            return false;
        }
		$this->order_id = $order[0]['ID'];
		
		return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:order_items,product_id,order_id,' . $this->order_id],
			'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            
            'currency' => ['required', 'string', 'size:3', Rule::in(config('payments.currencies.supported', ['EGP']))],
            'status' => ['required', 'string', Rule::in(['PENDING','PAID','SHIPPING','CANCELLED'])],
            
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.country' => ['nullable', 'string', 'max:100'],
            'shipping_address.city' => ['nullable', 'string', 'max:100'],
            'shipping_address.address_line1' => ['nullable', 'string', 'max:500'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            
            'billing_address' => ['nullable', 'array'],
            'billing_address.country' => ['nullable', 'string', 'max:100'],
            'billing_address.city' => ['nullable', 'string', 'max:100'],
            'billing_address.address_line1' => ['nullable', 'string', 'max:500'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            
            'discount' => ['nullable', 'array'],
            'discount.type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount.value' => ['nullable', 'numeric', 'min:0'],
            
			'tax' => ['nullable', 'array'],
            'tax.type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'tax.value' => ['nullable', 'numeric', 'min:0'],
            
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            
            'notes' => ['nullable', 'string', 'max:2000'],
            
        ];
    }
	
	public function messages(): array
    {
		return [
			'items.required' => 'يجب إضافة عناصر على الأقل للطلب',
            'items.*.product_id.required' => 'معرف المنتج مطلوب',
            'items.*.product_id.exists' => 'لم يتم التعرف على المنتج',
            'items.*.quantity.min' => 'الكمية يجب أن تكون على الأقل 1',
            'items.*.unit_price.min' => 'السعر يجب أن يكون أكبر من صفر',
            'currency.in' => 'العملة غير مدعومة',
        ];
    }
}
