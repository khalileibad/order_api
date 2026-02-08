<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order;

class ProcessPaymentRequest extends FormRequest
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
            'transaction_id' => "required|exists:payments,gateway_transaction_id,order_id,$this->order_id",
			'payment_token' => ['nullable', 'string'],
            
        ];
    }
	
	public function messages(): array
    {
		return [
			'transaction_id.required' => 'مرجع عملية الدفع مطلوب',
			'transaction_id.exists' => 'لم يتم التعرف على المرجع',
            
        ];
    }
}
