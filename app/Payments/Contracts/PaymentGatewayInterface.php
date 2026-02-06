<?php

namespace App\Payments\Contracts;

interface PaymentGatewayInterface
{
    public function processPayment(array $data): array;
    public function verifyPayment(string $transactionId): array;
    public function refundPayment(string $transactionId, float $amount = null): array;
    public function getGatewayInfo(): array;
    public function validateConfig(array $config): bool;
}