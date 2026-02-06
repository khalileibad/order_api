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
            
            $publicGateways = [];
            
            foreach ($gateways as $key => $gateway) {
                $publicGateways[$key] = [
                    'name' => $gateway['display_name'] ?? $key,
                    'description' => $gateway['display_name'] ?? $key,
                    'icon' => config("payments.gateways.{$key}.icon", '💳'),
                    'color' => config("payments.gateways.{$key}.color", '#6c757d'),
                    'enabled' => config("payments.gateways.{$key}.enabled", false),
                    'supported_methods' => config("payments.gateways.{$key}.supported_methods", []),
                    'min_amount' => config("payments.gateways.{$key}.min_amount", 1),
                    'max_amount' => config("payments.gateways.{$key}.max_amount", 50000),
                    'supported_currencies' => ['EGP', 'USD'], // افتراضي
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'gateways' => $publicGateways,
                    'default_currency' => config('payments.currencies.default', 'EGP'),
                    'supported_currencies' => config('payments.currencies.supported', ['EGP']),
                ],
                'meta' => [
                    'count' => count($publicGateways),
                    'requires_auth_for_payment' => true,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بوابات الدفع',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
	
    /**
     * 2. معلومات بوابة محددة (للعامة)
     * GET /api/payment/gateways/stripe
     *
    public function show($gateway)
    {
        try {
            if (!config("payments.gateways.{$gateway}.enabled", false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بوابة الدفع غير متاحة'
                ], 404);
            }
            
            $info = [
                'name' => config("payments.gateways.{$gateway}.name", $gateway),
                'description' => $this->getGatewayDescription($gateway),
                'icon' => config("payments.gateways.{$gateway}.icon", '💳'),
                'color' => config("payments.gateways.{$gateway}.color", '#6c757d'),
                'enabled' => true,
                'supported_methods' => config("payments.gateways.{$gateway}.supported_methods", []),
                'min_amount' => config("payments.gateways.{$gateway}.min_amount", 1),
                'max_amount' => config("payments.gateways.{$gateway}.max_amount", 50000),
                'supported_currencies' => ['EGP', 'USD'],
                'instructions' => $this->getGatewayInstructions($gateway),
                'test_mode' => config("payments.gateways.{$gateway}.mode") === 'sandbox',
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
    
    
    
    /**
     * الحصول على تعليمات البوابة
     *
    private function getGatewayInstructions($gateway): array
    {
        return [
            'test' => ['أدخل أي بيانات، سيتم محاكاة الدفع'],
            'stripe' => ['أدخل بيانات البطاقة', 'أكمل التحقق ثنائي العوامل إذا طُلب'],
            'paypal' => ['سيتم توجيهك لموقع PayPal', 'سجل الدخول وأكمل الدفع'],
        ][$gateway] ?? ['اتبع التعليمات على الشاشة'];
    }
	*/
}