<?php

namespace App\Payments\Gateways;

class TestGateway extends AbstractGateway
{
    protected string $name = 'test';
    protected string $displayName = 'البوابة التجريبية';
    
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
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'EGP',
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
}