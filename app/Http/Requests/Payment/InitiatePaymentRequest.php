<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order;

class InitiatePaymentRequest extends FormRequest
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
		$paymentManager = app('payment');
		$gateways = $paymentManager->getAvailableGateways();
            
        return [
            'payment_method' => ['sometimes', 'string', Rule::in(['credit_card', 'apple_pay','google_pay'])],
            'gateway' => ['required', 'string', Rule::in(array_keys($gateways))],
            
        ];
    }
	
	protected function prepareForValidation(): void
	{
		if ($this->has('payment_method')) {
			$this->merge(['payment_method' => strtolower($this->payment_method)]);
		}
		
		if ($this->has('gateway')) {
			$this->merge(['gateway' => strtolower($this->gateway)]);
		}
	}
	
	public function messages(): array
    {
		return [
			'gateway.required' => 'بوابة الدفع مطلوبة',
            'gateway.in' => 'بوابة الدفع غير مدعومة',
            'payment_method.in' => 'طريقة الدفع غير مدعومة',
        ];
    }
}
