<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentService
{
	protected $paymentGatewayService;
    
	public function __construct()
    {
        $this->paymentGatewayService = app('payment');
    }
	
	public function initiateCheckout($data)
    {
		try {
            $user = $data['user'];
			if ($data['user_role'] == 'admin') {
				$user = 0;
			}
				
			$order = Order::getDataWithDetails(0,$data['orderId'],$user);
			
			if (!$order) {
				return [
					'success' => false,
					'message' => 'الطلب غير موجود',
					'code' =>404
				];
			}
			$order = $order[0];
			
			$validationResult = $this->validateOrderForCheckout($order);
			
			if (!$validationResult['valid']) {
				return [
					'success' => false,
					'message' => $validationResult['message'],
					'data' => $validationResult['data'] ?? null,
					'code' =>400
					];
			}
			
			$gateway = $this->paymentGatewayService->driver($data['gateway']);
			
			if(!in_array($data['payment_method'],$gateway->getSupportedMethod()))
			{
				return [
                    'success' => false,
                    'message' => 'طريقة الدفع غير مدعومة في هذه البوابة',
                    'supported_methods' => $gateway->getSupportedMethod(),
					'code' =>400
				];
			}
			
			return DB::transaction(function () use ($data,$order,$gateway) {
				
				$transaction = $this->createTransaction($order, $data);
				
				$paymentData = $this->preparePaymentData($order, $transaction, $data);
				
				//processing Payment
				$paymentResult = $gateway->processPayment($paymentData);
				
				//Update Payment transaction after Payment
				$transaction->update([
					'gateway_transaction_id' => $paymentResult['gateway_transaction_id'] ?? null,
					'gateway_reference' => $paymentResult['reference'] ?? null,
					'payment_url' => $paymentResult['payment_url'] ?? null,
					'gateway_response' => $paymentResult,
					'status' => $paymentResult['status'] ?? 'pending',
					'expires_at' => $paymentResult['expires_at'] ?? now()->addMinutes(30),
				]);
				
				
				return [
					'success' => true,
					'payment' => Payment::getDataWithDetails($transaction->pay_id),
					'order' => Order::getDataWithDetails($order['ID']),
					
				];
			});
		
		} catch (\Exception $e) {
            \Log::error('Initiate checkout failed: ' . $e->getMessage(), [
                'order' => $data['orderId'],
                'exception' => $e,
            ]);
            
            return [
                'success' => false,
                'message' => 'فشل في بدء عملية الدفع',
                'error' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع',
				'code' =>500
            ];
        }
		
	}
    
	private function validateOrderForCheckout($order)
    {
        if($order['STATUS'] != 'PENDING') {
            return [
                'valid' => false,
                'message' => 'لا يمكن الدفع للطلب في حالته الحالية',
                'data' => [
                    'current_status' => $order['STATUS'],
                    'allowed_statuses' => 'PENDING'
                ]
            ];
        }
        
        if ($order['SUB_AMOUNT'] <= 0) {
            return [
                'valid' => false,
                'message' => 'المبلغ الإجمالي للطلب غير صالح للدفع',
                'data' => [
                    'total_amount' => $order['SUB_AMOUNT']
                ]
            ];
        }
        
        if (empty($order['ITEMS'])) {
            return [
                'valid' => false,
                'message' => 'الطلب لا يحتوي على عناصر',
                'data' => [
                    'items_count' => 0
                ]
            ];
        }
        
        if (!empty($order['PAYMENT'])) {
            return [
                'valid' => false,
                'message' => 'هنالك عملية دفع مسبقة في هذا الطلب',
                'data' => [
                    'items_count' => 0
                ]
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'الطلب صالح للدفع'
        ];
    }
	
	//New Payment Transaction
	private function createTransaction($order , array $data)
    {
        $transactionId = 'TRX-' . strtoupper(Str::random(8)) . '-' . time();
        
        return Payment::create([
            'order_id' => $order['ID'],
            'transaction_id' => $transactionId,
            'gateway' => $data['gateway'],
            'payment_method' => $data['payment_method'],
            'amount' => $order['TOTAL'],
            'currency' => $order['CURRENCY'],
            'status' => 'initiated',
            'description' => "دفع للطلب #".$order['NUMBER'],
            'cutomer' => $order['CUSTOMER'],
            'metadata' => json_encode([
                'items_count' => count($order['ITEMS']),
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
            ]),
            'expires_at' => now()->addMinutes(30),
        ]);
    }
    
    private function preparePaymentData($order, $transaction, array $data)
    {
        return [
            'transaction_id' => $transaction->transaction_id,
            'order_id' => $order['ID'],
            'order_number' => $order['NUMBER'],
            'amount' => $order['TOTAL'],
            'currency' => $order['CURRENCY'],
            'customer' => [
                'id' => $order['CUSTOMER'],
                'name' => $order['CUSTOMER_NAME'],
                'email' => $order['CUSTOMER_EMAIL'],
                'phone' => $order['CUSTOMER_PHONE'],
            ],
            'billing_address' => $order['BILLING_ADD'],
            'shipping_address' => $order['SHIPPING_ADD'],
            'items' => $order['ITEMS'],
            'payment_method' => $data['payment_method'],
            'card_details' => $this->prepareCardDetails($data),
            'return_url' => $data['return_url'] ?? config('app.url') . '/payment/success',
            'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/payment/cancel',
            'metadata' => $transaction->metadata ?? [],
        ];
    }
    
    private function prepareCardDetails(array $data)
    {
        $paymentMethod = $data['payment_method'];
        
        if (!in_array($paymentMethod, ['credit_card', 'mada'])) {
            return null;
        }
        
        return [
            'number' => $data['card_number'] ?? null,
            'holder' => $data['card_holder'] ?? null,
            'expiry_month' => $data['expiry_month'] ?? null,
            'expiry_year' => $data['expiry_year'] ?? null,
            'cvv' => $data['cvv'] ?? null,
        ];
    }
    
}