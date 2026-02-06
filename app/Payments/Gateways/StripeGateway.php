<?php

namespace App\Payments\Gateways;

class StripeGateway extends AbstractGateway
{
    protected string $name = 'stripe';
    protected string $displayName = 'Stripe';
    
    public function processPayment(array $data): array
    {
		return [
            'success' => true,
            'transaction_id' => 'STRIPE_' . uniqid(),
            'payment_intent' => 'pi_' . uniqid(),
            'client_secret' => 'pi_' . uniqid() . '_secret_' . uniqid(),
            'message' => 'يرجى إكمال الدفع عبر Stripe',
            'requires_action' => true,
            'gateway' => $this->name,
        ];
    }
    
    public function verifyPayment(string $transactionId): array
    {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'succeeded',
            'amount' => 1000.00,
            'currency' => 'usd',
        ];
    }
    
    public function refundPayment(string $transactionId, float $amount = null): array
    {
        return [
            'success' => true,
            'refund_id' => 're_' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'message' => 'تم استرداد المبلغ عبر Stripe',
        ];
    }
    
    protected function getRequiredConfig(): array
    {
        return ['secret_key', 'publishable_key'];
    }
}