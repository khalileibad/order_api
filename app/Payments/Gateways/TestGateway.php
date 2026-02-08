<?php

namespace App\Payments\Gateways;

class TestGateway extends AbstractGateway
{
    protected string $name;
    protected string $displayName;
    protected string $note;
    
	function __construct()
	{
		$this->name = env('PAYMENT_TEST_NAME', 'Test');
		$this->displayName = env('PAYMENT_TEST_NAME', 'البوابة التجريبية');
		$this->note = env('PAYMENT_TEST_NOTE', 'البوابة التجريبية');
	}
	
	public function initiatePayment(array $data): array
    {
		$paymentIntentId = 'TEST_' . strtoupper(uniqid());
		$clientSecret = $paymentIntentId . '_secret_' . strtoupper(uniqid());
            
		return [
			'gateway' => $this->name,
			'transaction_id' => $paymentIntentId,
			'client_secret' => $clientSecret,
			'simulated' => true,
			'amount' => $data['TOTAL'] ?? 0,
			'currency' => $data['CURRENCY'] ?? 'EGP',
		];
	}
	
	public function processPayment(array $data): array
    {
        $success = ($data['amount'] ?? 0) > 0;
        
        return [
            'success' => $success,
            'transaction_id' => 'TEST_' . uniqid(),
            'reference' => 'TEST_REF_' . time(),
            'message' => $success ? 'تمت معالجة الدفع بنجاح (تجريبي)' : 'فشل الدفع (تجريبي)',
            'gateway_response' => [
                'simulated' => true,
				'amount' => $data['TOTAL'] ?? 0,
				'currency' => $data['CURRENCY'] ?? 'EGP',
            ],
            'gateway' => $this->name,
        ];
    }
    
    public function verifyPayment(string $transactionId): array
    {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'paid',
            'amount' => 100.00,
            'verified_at' => now()->toDateTimeString(),
        ];
    }
    
    public function refundPayment(string $transactionId, float $amount = null): array
    {
        return [
            'success' => true,
            'refund_id' => 'REFUND_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'message' => 'تم استرداد المبلغ (تجريبي)',
        ];
    }
    
    protected function getRequiredConfig(): array
    {
        return []; // لا يحتاج إعدادات
    }
	
	public function getSupportedMethod(): array
    {
		return $this->config['supported_methods'];
	}
	
	
}