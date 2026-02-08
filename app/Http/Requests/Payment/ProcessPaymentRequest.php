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
            'gateway_reference' => ['required', 'string'],
            'gateway_transaction_id' => ['nullable', 'string'],
            'payment_id' => ['required', 'string'],
            
			'gateway_response' => ['nullable', 'array'],
            'gateway_response.*' => ['nullable'],
            
			'card_token' => ['nullable', 'string'],
            'save_card' => ['boolean'],
            
        ];
    }
	
	public function messages(): array
    {
		return [
			'gateway_reference.required' => 'مرجع عملية الدفع مطلوب',
            'verification_token.required' => 'رمز التحقق مطلوب للطلبات غير المسجلة',
            
        ];
    }
}
