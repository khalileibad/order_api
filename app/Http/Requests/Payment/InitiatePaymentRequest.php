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
            'payment_method' => ['required', 'string', Rule::in(['credit_card', 'apple_pay'])],
            'gateway' => ['required', 'string', Rule::in(array_keys($gateways))],
            
            'card_number' => ['nullable', 'string', 'required_if:payment_method,credit_card,mada', 'size:16'],
            'card_holder' => ['nullable', 'string', 'required_if:payment_method,credit_card,mada', 'max:255'],
            'expiry_month' => ['nullable', 'integer', 'required_if:payment_method,credit_card,mada', 'min:1', 'max:12'],
            'expiry_year' => ['nullable', 'integer', 'required_if:payment_method,credit_card,mada', 'min:' . date('Y')],
            'cvv' => ['nullable', 'string', 'required_if:payment_method,credit_card,mada', 'size:3'],
            
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
    
		if ($this->has('card_number')) {
			$this->merge(['card_number' => preg_replace('/\s+/', '', $this->card_number)]);
		}
	}
	
	public function messages(): array
    {
		return [
			'payment_method.required' => 'طريقة الدفع مطلوبة',
            'gateway.required' => 'بوابة الدفع مطلوبة',
            'gateway.in' => 'بوابة الدفع غير مدعومة',
            'card_number.required_if' => 'رقم البطاقة مطلوب لطريقة الدفع المختارة',
            'card_number.size' => 'رقم البطاقة يجب أن يكون 16 رقم',
            'cvv.size' => 'رقم CVV يجب أن يكون 3 أرقام',
            'expiry_year.min' => 'سنة انتهاء البطاقة غير صالحة',
        ];
    }
}
