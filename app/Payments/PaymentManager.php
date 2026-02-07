<?php
namespace App\Payments;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\Gateways\TestGateway;
use App\Payments\Gateways\StripeGateway;
use App\Payments\Gateways\PayPalGateway;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function getDefaultDriver()
    {
        return config('payments.default', 'test');
    }
    
    public function createTestDriver()
    {
        return new TestGateway(config('payments.gateways.test', []));
    }
    
    public function createStripeDriver()
    {
		return new StripeGateway(config('payments.gateways.stripe', []));
    }
    
    public function createPaypalDriver()
    {
        return new PayPalGateway(config('payments.gateways.paypal', []));
    }
    
    public function getAvailableGateways(): array
    {
        $gateways = [];
        
        foreach (config('payments.gateways', []) as $key => $config) {
            if ($config['enabled'] ?? false) {
                $gateway = $this->driver($key);
                $gateways[$key] = $gateway->getGatewayInfo();
            }
        }
        
        return $gateways;
    }
    
    public function extendGateway(string $name, \Closure $callback): void
    {
        $this->extend($name, $callback);
    }
}