<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
    
    /**
     * 3. بدء عملية الدفع (يتطلب تسجيل دخول)
     * POST /api/orders/{order}/pay
     *
    public function initiate(Request $request, $orderId)
    {
        // هذا يحتاج auth:api middleware
        // ... كود بدء الدفع
    }
    
    
    
    
	*/
}