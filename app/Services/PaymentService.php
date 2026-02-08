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
	
	public function initiatePayment($data)
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
			$methods = $gateway->getSupportedMethod();
			if(!empty($methods) && !in_array($data['payment_method'],$methods))
			{
				return [
                    'success' => false,
                    'message' => 'طريقة الدفع غير مدعومة في هذه البوابة',
                    'supported_methods' => $methods,
					'code' =>400
				];
			}
			
			return DB::transaction(function () use ($data,$order,$gateway) {
				
				$transactionId = 'TRX-' . strtoupper(Str::random(8)) . '-' . time();
				
				$paymentInitiate = $gateway->initiatePayment($order);
				
				$transaction = Payment::create([
					'order_id' => $order['ID'],
					'transaction_id' => $transactionId,
					'gateway' => $data['gateway'],
					'payment_method' => $data['payment_method'],
					'amount' => $order['TOTAL'],
					'currency' => $order['CURRENCY'],
					'status' => 'initiated',
					'description' => "دفع للطلب #".$order['NUMBER'],
					'cutomer' => $order['CUSTOMER'],
					'gateway_transaction_id' => $paymentInitiate['transaction_id']?? null,
					'payment_url' => $paymentInitiate['approval_url']?? null,
					'metadata' => json_encode([
						'items_count' => count($order['ITEMS']),
						'ip_address' => $data['ip_address'] ?? null,
						'user_agent' => $data['user_agent'] ?? null,
						'gateway_response' =>$paymentInitiate
					]),
					'initiate_at' => now(),
					'expires_at' => now()->addMinutes(30),
				]);
				
				return [
					'success' => true,
					'gateway' => $paymentInitiate,
					'order' => Order::getDataWithDetails($order['ID'])[0],
					
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
    
	public function processPayment($data)
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
			
			$validationResult = $this->validateOrderForProcessing($order,$data);
			
			if (!$validationResult['valid']) {
				return [
					'success' => false,
					'message' => $validationResult['message'],
					'data' => $validationResult['data'] ?? null,
					'code' =>400
					];
			}
			$gateway = $this->paymentGatewayService->driver($order['GATEWAY']);
			
			return DB::transaction(function () use ($data,$order,$gateway) {
				
				$paymentData = $this->preparePaymentData($order, $data);
				//processing Payment
				$paymentResult = $gateway->processPayment($paymentData);
				
				$gateway = $this->paymentGatewayService->driver($order['GATEWAY']);
				
				$meta = $order['P_META'] ?? "{}";
				$meta = (array) json_decode($meta);
				\Log::info(gettype($meta));
				
				$meta = array_merge($meta,['gateway_payment_response' => $paymentResult]);
				\Log::info($meta);
				//Update Payment transaction after Payment
				$transaction = Payment::findOrFail($order['PAYMENT']);
				$transaction->update([
					'gateway_transaction_id' => $paymentResult['gateway_transaction_id'] ?? null,
					'gateway_reference' => $paymentResult['reference'] ?? null,
					'payment_url' => $paymentResult['payment_url'] ?? null,
					'gateway_response' => $paymentResult,
					'status' => $paymentResult['status'] ?? 'pending',
					'paid_at' => ($paymentResult['status'] == 'paid')? now() : null,
					'expires_at' => $paymentResult['expires_at'] ?? now()->addMinutes(30),
					'metadata' => json_encode($meta),
				]);
				
				if($paymentResult['status'] == 'paid'){
					$upd_order = Order::findOrFail($order['ID']);
					$upd_order->status = "PAIED";
					$upd_order->save();
					
					$Product = \App\Models\Product;
					foreach ($order['ITEMS'] as $item) {
						$Product::where('pro_id', $item['PRODUCT'])->decrement('pro_stock', $item['QUANTITY']);
					}
				}
					
				return [
					'success' => true,
					'order' => Order::getDataWithDetails($order['ID']),
					'payment' => $paymentResult,
					
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
	
	private function validateOrderForProcessing($order,$data)
	{
		if (empty($order['PAYMENT'])) {
			return [
				'valid' => false,
				'message' => 'ليجب بدء عملية الدفع أولاً',
				'data' => [
					'current_status' => $order['STATUS'],
					'initiate_url' => route('api.payments.initiate', $order->id)
				]
			];
		}
		if ($order['EXIRES'] && strtotime($order['EXIRES']) < time()) {
			return [
				'valid' => false,
				'message' => 'انتهت صلاحية الطلب',
				'data' => [
					'order_expired' => true,
					'expired_at' => $order->expires_at->toDateTimeString()
				]
			];
		}
    
		if ($order['STATUS'] != 'PENDING') {
			return [
				'valid' => false,
				'message' => 'لا يمكن معالجة الدفع للطلب في حالته الحالية',
				'data' => [
					'current_status' => $order['STATUS'],
				]
			];
		}
		
		if($order['P_STATUS'] && $order['P_STATUS'] === 'paid') {
			return [
				'valid' => false,
				'message' => 'تم دفع الطلب مسبقاً',
				'data' => [
					'payment_status' => 'paid',
					'paid_at' => $order['PAIED']
				]
			];
		}
    
		if($order['TOTAL'] <= 0) {
			return [
				'valid' => false,
				'message' => 'المبلغ الإجمالي للطلب غير صالح للدفع',
				'data' => [
					'total_amount' => $order['TOTAL']
				]
			];
		}
		
		if($order['G_TRANSACTION'] != $data['transaction_id']) {
			return [
				'valid' => false,
				'message' => 'رقم تحويلة البوابة غير صحيح',
				'data' => [
					'TRANSACTION' => $order['G_TRANSACTION']
				]
			];
		}
		
		return [
			'valid' => true,
			'message' => 'الطلب صالح للمعالجة'
		];
	}
	
	private function preparePaymentData($order, array $data)
    {
        return [
            'transaction_id' => $order['TRANSACTION'],
            'payment_token' => $data['payment_token'],
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
            'payment_method' => $order['P_METHOD'],
            'return_url' => $data['return_url'] ?? config('app.url') . '/payment/success',
            'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/payment/cancel',
            'metadata' => $order['P_META'] ?? [],
        ];
    }
    
	
}