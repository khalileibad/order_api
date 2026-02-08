<?php
namespace App\Payments\Gateways;

use App\Payments\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $name;
    protected string $displayName;
    protected string $note;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->validateConfig($config);
		
		$this->name = env('PAYMENT_STRIPE_NAME', 'stripe');
		$this->displayName = env('PAYMENT_STRIPE_NAME', 'Stripe');
		$this->note = env('PAYMENT_STRIPE_NOTE', 'Stripe Gateway');
    }
    
    public function processPayment(array $data): array
    {
        try {
            $success = $this->simulateStripePayment($data);
            
            if (!$success) {
                return [
                    'success' => false,
                    'error' => 'payment_failed',
                    'error_message' => 'Failed to process payment via Stripe',
                    'decline_code' => 'insufficient_funds',
                    'gateway' => $this->name,
                    'simulation' => true,
                ];
            }
            
            $paymentIntentId = 'pi_' . strtoupper(uniqid());
            $clientSecret = $paymentIntentId . '_secret_' . strtoupper(uniqid());
            
            return [
                'success' => true,
                'transaction_id' => $paymentIntentId,
                'payment_intent' => $paymentIntentId,
                'client_secret' => $clientSecret,
                'amount' => $data['amount'] ?? 0,
                'currency' => strtolower($data['currency'] ?? 'EGP'),
                'status' => 'requires_payment_method',
                'requires_action' => true,
                'next_action' => [
                    'type' => 'redirect_to_url',
                    'redirect_to_url' => [
                        'url' => $this->config['return_url'] ?? '',
                        'return_url' => $this->config['return_url'] ?? '',
                    ],
                ],
                'message' => 'تم إنشاء Payment Intent بنجاح',
                'gateway' => $this->name,
                'simulation' => [
                    'is_simulated' => true,
                    'note' => 'في الإنتاج، استبدل بـ Stripe SDK الحقيقي',
                    'docs' => 'https://stripe.com/docs/payments/accept-a-payment',
                ],
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'gateway_error',
                'error_message' => $e->getMessage(),
                'gateway' => $this->name,
            ];
        }
    }
    
    public function verifyPayment(string $transactionId): array
    {
        $statuses = ['succeeded', 'processing', 'requires_action', 'canceled'];
        $status = $statuses[array_rand($statuses)];
        
        return [
            'success' => $status === 'succeeded',
            'transaction_id' => $transactionId,
            'payment_intent' => $transactionId,
            'status' => $status,
            'amount' => 1000.00,
            'currency' => 'EGP',
            'captured' => $status === 'succeeded',
            'customer' => 'cus_' . uniqid(),
            'created' => time(),
            'gateway' => $this->name,
            'simulation' => true,
        ];
    }
    
    public function refundPayment(string $transactionId, float $amount = null): array
    {
        return [
            'success' => true,
            'refund_id' => 're_' . strtoupper(uniqid()),
            'transaction_id' => $transactionId,
            'payment_intent' => $transactionId,
            'amount' => $amount ?? 500.00,
            'currency' => 'EGP',
            'status' => 'succeeded',
            'reason' => 'requested_by_customer',
            'receipt_number' => strtoupper(uniqid()),
            'created' => time(),
            'message' => 'تم استرداد المبلغ بنجاح',
            'gateway' => $this->name,
            'simulation' => true,
        ];
    }
    
    public function getGatewayInfo(): array
    {
        return [
            'name' => $this->name,
            'display_name' => $this->displayName,
            'note' => $this->note,
            'supports_refund' => true,
            'supports_partial_refund' => true,
            'supports_3d_secure' => true,
            'config_keys' => $this->getRequiredConfig(),
            'features' => [
                'credit_cards' => true,
                'apple_pay' => true,
                'google_pay' => true,
                'recurring_payments' => true,
                'international' => true,
            ],
            'supported_currencies' => ['EGP', 'USD', 'EUR', 'GBP'],
            'supported_countries' => ['EG', 'US', 'GB', 'AE'],
            'documentation' => 'https://stripe.com/docs',
        ];
    }
    
	public function getSupportedMethod(): array
    {
		return $this->config['supported_methods'];
	}
	
	public function validateConfig(array $config): bool
    {
        $required = $this->getRequiredConfig();
        
        if (app()->environment('local', 'development', 'testing')) {
            return true;
        }
        
        foreach ($required as $key) {
            if (empty($config[$key] ?? '')) {
                throw new \InvalidArgumentException("Stripe gateway missing required config: {$key}");
            }
        }
        
        return true;
    }
    
    protected function getRequiredConfig(): array
    {
        return ['secret_key', 'publishable_key'];
    }
    
	private function simulateStripePayment(array $data): bool
    {
        $amount = $data['amount'] ?? 0;
        
        $rules = [
            'amount_positive' => $amount > 0,
            'amount_within_limit' => $amount <= 1000000,
            'currency_supported' => in_array(strtoupper($data['currency'] ?? 'EGP'), ['EGP', 'USD', 'EUR']),
            'success_rate' => mt_rand(1, 100) <= 95,
        ];
        
        if (isset($data['payment_method']['card']['number'])) {
            $cardNumber = $data['payment_method']['card']['number'];
            if (in_array($cardNumber, ['4000000000000002', '4000000000009995'])) {
                return false;
            }
        }
        
        return !in_array(false, $rules, true);
    }
}