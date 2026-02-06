<?php

namespace App\Payments\Gateways;

use App\Payments\Contracts\PaymentGatewayInterface;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $name;
    protected string $displayName;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->validateConfig($config);
    }
    
    public function validateConfig(array $config): bool
    {
        $required = $this->getRequiredConfig();
        
        foreach ($required as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                throw new \InvalidArgumentException("Missing required config: {$key}");
            }
        }
        
        return true;
    }
    
    public function getGatewayInfo(): array
    {
        return [
            'name' => $this->name,
            'display_name' => $this->displayName,
            'supports_refund' => method_exists($this, 'refundPayment'),
            'config_keys' => $this->getRequiredConfig(),
        ];
    }
    
    abstract protected function getRequiredConfig(): array;
}