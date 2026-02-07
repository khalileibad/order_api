<?php
namespace App\Payments\Gateways;

use App\Payments\Contracts\PaymentGatewayInterface;

class PayPalGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $name;
    protected string $displayName;
    protected string $note;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->validateConfig($config);
		
		$this->name = env('PAYMENT_PAYPAL_NAME', 'paypal');
		$this->displayName = env('PAYMENT_PAYPAL_NAME', 'PayPal');
		$this->note = env('PAYMENT_PAYPAL_NOTE', 'PayPal Gateway');
    }
    
    public function processPayment(array $data): array
    {
        try {
            $success = $this->simulatePayPalPayment($data);
            
            if (!$success) {
                return [
                    'success' => false,
                    'error' => 'PAYMENT_FAILED',
                    'error_message' => 'فشل في إنشاء طلب الدفع عبر PayPal',
                    'gateway' => $this->name,
                    'simulation' => true,
                ];
            }
            
            $paymentId = 'PAY-' . strtoupper(uniqid());
            $approvalToken = 'EC-' . strtoupper(uniqid());
            
            $mode = $this->config['mode'] ?? 'sandbox';
            $baseUrl = $mode === 'sandbox' 
                ? 'https://www.sandbox.paypal.com' 
                : 'https://www.paypal.com';
            
            return [
                'success' => true,
                'transaction_id' => $paymentId,
                'payment_id' => $paymentId,
                'approval_token' => $approvalToken,
                'approval_url' => "{$baseUrl}/checkoutnow?token={$approvalToken}",
                'payer_id' => 'PAYER_' . strtoupper(uniqid()),
                'intent' => 'sale',
                'state' => 'created',
                'amount' => [
                    'total' => $data['amount'] ?? 0,
                    'currency' => strtoupper($data['currency'] ?? 'EGP'),
                ],
                'redirect_urls' => [
                    'return_url' => $this->config['return_url'] ?? '',
                    'cancel_url' => $this->config['cancel_url'] ?? '',
                ],
                'message' => 'تم إنشاء طلب دفع PayPal، يرجى التوجيه للموافقة',
                'gateway' => $this->name,
                'simulation' => [
                    'is_simulated' => true,
                    'mode' => $mode,
                    'sandbox_url' => 'https://sandbox.paypal.com',
                    'live_url' => 'https://paypal.com',
                    'note' => 'في الإنتاج، استبدل بـ PayPal SDK الحقيقي',
                ],
                'next_steps' => [
                    '1. توجيه المستخدم إلى approval_url',
                    '2. انتظار callback على return_url',
                    '3. استدعاء verifyPayment لتأكيد الدفع',
                ],
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'PAYPAL_ERROR',
                'error_message' => $e->getMessage(),
                'gateway' => $this->name,
            ];
        }
    }
    
    public function verifyPayment(string $transactionId): array
    {
        $statuses = ['approved', 'created', 'failed', 'expired'];
        $status = $statuses[array_rand($statuses)];
        
        return [
            'success' => $status === 'approved',
            'transaction_id' => $transactionId,
            'payment_id' => $transactionId,
            'state' => $status,
            'intent' => 'sale',
            'amount' => [
                'total' => 1000.00,
                'currency' => 'EGP',
            ],
            'payer' => [
                'payer_id' => 'PAYER_' . uniqid(),
                'email' => 'payer@example.com',
                'name' => 'John Doe',
            ],
            'create_time' => date('Y-m-d\TH:i:s\Z'),
            'update_time' => date('Y-m-d\TH:i:s\Z'),
            'links' => [
                [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/' . $transactionId,
                    'rel' => 'self',
                    'method' => 'GET',
                ],
            ],
            'gateway' => $this->name,
            'simulation' => true,
        ];
    }
    
    public function refundPayment(string $transactionId, float $amount = null): array
    {
        return [
            'success' => true,
            'refund_id' => 'REF-' . strtoupper(uniqid()),
            'sale_id' => $transactionId,
            'parent_payment' => $transactionId,
            'amount' => [
                'total' => $amount ?? 500.00,
                'currency' => 'EGP',
            ],
            'state' => 'completed',
            'reason' => 'Refund for order',
            'create_time' => date('Y-m-d\TH:i:s\Z'),
            'update_time' => date('Y-m-d\TH:i:s\Z'),
            'message' => 'تم استرداد المبلغ عبر PayPal',
            'gateway' => $this->name,
            'simulation' => true,
        ];
    }
    
    public function getGatewayInfo(): array
    {
        $mode = $this->config['mode'] ?? 'sandbox';
        
        return [
            'name' => $this->name,
            'display_name' => $this->displayName,
            'mode' => $mode,
            'supports_refund' => true,
            'supports_partial_refund' => true,
            'config_keys' => $this->getRequiredConfig(),
            'features' => [
                'paypal_account' => true,
                'credit_cards' => true,
                'express_checkout' => true,
                'invoicing' => true,
                'recurring_payments' => true,
            ],
            'supported_currencies' => ['EGP', 'USD', 'EUR', 'AUD', 'CAD'],
            'supported_countries' => ['SA', 'US', 'GB', 'AE', 'CA', 'AU'],
            'api_endpoints' => [
                'sandbox' => 'https://api.sandbox.paypal.com',
                'live' => 'https://api.paypal.com',
            ],
            'documentation' => 'https://developer.paypal.com/docs/api/overview/',
        ];
    }
    
    public function validateConfig(array $config): bool
    {
        $required = $this->getRequiredConfig();
        
        // في بيئة التطوير، يمكن السماح بدون مفاتيح
        if (app()->environment('local', 'development', 'testing')) {
            return true;
        }
        
        foreach ($required as $key) {
            if (empty($config[$key] ?? '')) {
                throw new \InvalidArgumentException("PayPal gateway missing required config: {$key}");
            }
        }
        
        return true;
    }
    
    protected function getRequiredConfig(): array
    {
        return ['client_id', 'client_secret'];
    }
    
    private function simulatePayPalPayment(array $data): bool
    {
        $amount = $data['amount'] ?? 0;
        
        $rules = [
            'amount_positive' => $amount > 0,
            'amount_within_limit' => $amount <= 500000, // 500,000
            'currency_supported' => in_array(strtoupper($data['currency'] ?? 'EGP'), ['EGP', 'USD', 'EUR']),
            'success_rate' => mt_rand(1, 100) <= 90, // 90% نجاح
        ];
        
        if (isset($data['test_failure']) && $data['test_failure'] === true) {
            return false;
        }
        
        return !in_array(false, $rules, true);
    }
}