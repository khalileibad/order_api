<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Services\PaymentService;

class PaymentController extends Controller
{
	protected $paymentService;
	
	public function __construct(PaymentService $paymentService)
	{
		$this->paymentService = $paymentService;
	}
	
	//Get payment gateways
	public function index(Request $request)
	{
		try {
			$paymentManager = app('payment');
            $gateways = $paymentManager->getAvailableGateways();
            
			$gateways_info = [];
            foreach ($gateways as $key => &$gateway) {
                $config = config("payments.gateways.{$key}", []);
				
				$gateways_info[$key] = [
					'name' => $config['name']?? $gateway,
					'description' => $config['description']?? $gateway,
					'icon' => $config['icon']?? '💳',
					'color' => $config['color']?? '#6c757d',
					'enabled' => true,
					'supported_methods' => $config['supported_methods']?? [],
					'min_amount' => $config['min_amount']?? 1,
					'max_amount' => $config['max_amount']?? 50000,
					'currency' => $config['currency']?? 'EGP',
					'instructions' => $config['Instructions']?? [],
					'test_mode' => $config['mode'] === 'sandbox',
					'simulation_info' =>[
						'is_simulated' => true,
						'purpose' => 'التطوير والاختبار',
						'production_ready' => false,
					]
				];
				
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'gateways' => $gateways_info,
                    'simulation_mode' => config('payments.simulation.enabled', true),
					'default_currency' => config('payments.currencies.default', 'EGP'),
                    'supported_currencies' => config('payments.currencies.supported', ['EGP']),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بوابات الدفع',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
	public function show($gateway)
    {
        try {
            if (!config("payments.gateways.{$gateway}.enabled", false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بوابة الدفع غير متاحة'
                ], 404);
            }
            
			$config = config("payments.gateways.{$gateway}", []);
				
			$info = [
					'name' => $config['name']?? $gateway,
					'description' => $config['description']?? $gateway,
					'icon' => $config['icon']?? '💳',
					'color' => $config['color']?? '#6c757d',
					'enabled' => true,
					'supported_methods' => $config['supported_methods']?? [],
					'min_amount' => $config['min_amount']?? 1,
					'max_amount' => $config['max_amount']?? 50000,
					'currency' => $config['currency']?? 'EGP',
					'instructions' => $config['Instructions']?? [],
					'test_mode' => $config['mode'] === 'sandbox',
					'simulation_info' =>[
						'is_simulated' => true,
						'purpose' => 'التطوير والاختبار',
						'production_ready' => false,
					]
				];
			
            return response()->json([
                'success' => true,
                'data' => $info
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب معلومات بوابة الدفع',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function initiate(InitiatePaymentRequest $request, $orderId)
    {
		try {
            $data = $request->validated();
			$data['orderId'] = $orderId;
			if(!auth()->check())
			{
				return response()->json([
                    'success' => false,
                    'message' => "No Permission",
                ], 403);
			}
			$data['user'] = auth()->user()->id;
			$data['user_role'] = auth()->user()->role;
			$data['request_info'] = [
				'user_agent' => $request->userAgent(),
				'ip_address' => $request->ip(),
				'method' => $request->method(),
				'source' => 'api',
			];
			
			$PaymentResult = $this->paymentService->initiatePayment($data);
            
			if(!$PaymentResult['success'])
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في بدء عملية الدفع',
					'data' => $PaymentResult['data'] ?? 'حدث خطأ غير متوقع',
					'error' => $PaymentResult['message'] ?? 'حدث خطأ غير متوقع'
				], 500);
			}
			
            return response()->json([
                'success' => true,
                'message' => 'تم بدء عملية الدفع',
                'data' => [
                    'order' => $PaymentResult['order'],
					'gateway' => $PaymentResult['gateway'],
                    
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Initiate checkout failed: ' . $e->getMessage(), [
                'order' => $orderId,
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء عملية الدفع',
                'error' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع'
            ], 500);
        }
    }
	
	public function processPayment(ProcessPaymentRequest $request, $orderId)
	{
		try {
            $data = $request->validated();
			$data['orderId'] = $orderId;
			if(!auth()->check())
			{
				return response()->json([
                    'success' => false,
                    'message' => "No Permission",
                ], 403);
			}
			$data['user'] = auth()->user()->id;
			$data['user_role'] = auth()->user()->role;
			$data['request_info'] = [
				'user_agent' => $request->userAgent(),
				'ip_address' => $request->ip(),
				'method' => $request->method(),
				'source' => 'api',
			];
			
			$PaymentResult = $this->paymentService->initiateCheckout($data);
            
			if(!$PaymentResult['success'])
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في بدء عملية الدفع',
					'data' => $PaymentResult['data'] ?? 'حدث خطأ غير متوقع',
					'error' => $PaymentResult['message'] ?? 'حدث خطأ غير متوقع'
				], 500);
			}
			
            return response()->json([
                'success' => true,
                'message' => 'تم بدء عملية الدفع',
                'data' => [
                    'payment' => $PaymentResult['payment'],
                    'order' => $PaymentResult['order'],
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Initiate checkout failed: ' . $e->getMessage(), [
                'order' => $orderId,
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء عملية الدفع',
                'error' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع'
            ], 500);
        }
	}
    
}