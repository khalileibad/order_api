<?php

return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'test'),
    
    'gateways' => [
        'test' => [
            'enabled' => env('PAYMENT_TEST_ENABLED', true),
            'name' => env('PAYMENT_TEST_NAME', 'Test Gateway'),
            'note' => env('PAYMENT_TEST_NOTE', 'Test NOTE'),
            'description' => env('PAYMENT_TEST_DESCRIPTION', 'Gateway for testing purposes'),
            'mode' => env('PAYMENT_TEST_MODE', 'sandbox'),
            'icon' => env('PAYMENT_TEST_ICON', '🧪'),
            'color' => env('PAYMENT_TEST_COLOR', '#6c757d'),
            'supported_methods' => explode(',', env('PAYMENT_TEST_METHODS', 'credit_card,test_card')),
            'min_amount' => env('PAYMENT_TEST_MIN_AMOUNT', 1),
            'max_amount' => env('PAYMENT_TEST_MAX_AMOUNT', 10000),
            'success_rate' => env('PAYMENT_TEST_SUCCESS_RATE', 100),
            'Instructions' => explode(',', env('PAYMENT_TEST_INSTRUCTIONS', 'اتبع التعليمات على الشاشة')),
        ],
        
        'stripe' => [
            'enabled' => env('PAYMENT_STRIPE_ENABLED', false),
            'name' => env('PAYMENT_STRIPE_NAME', 'Stripe'),
            'note' => env('PAYMENT_STRIPE_NOTE', 'Stripe NOTE'),
            'description' => env('PAYMENT_STRIPE_DESCRIPTION', 'Secure credit card payments'),
            'secret_key' => env('STRIPE_SECRET_KEY', env('STRIPE_API_SECRET', '')),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', env('STRIPE_API_KEY', '')),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'mode' => env('STRIPE_MODE', 'sandbox'),
            'icon' => env('PAYMENT_STRIPE_ICON', '💳'),
            'color' => env('PAYMENT_STRIPE_COLOR', '#6772e5'),
            'supported_methods' => explode(',', env('PAYMENT_STRIPE_METHODS', 'credit_card,apple_pay,google_pay')),
            'min_amount' => env('PAYMENT_STRIPE_MIN_AMOUNT', 1),
            'max_amount' => env('PAYMENT_STRIPE_MAX_AMOUNT', 50000),
            'currency' => env('PAYMENT_STRIPE_CURRENCY', 'EGP'),
            'Instructions' => explode(',', env('PAYMENT_STRIPE_INSTRUCTIONS', 'اتبع التعليمات على الشاشة')),
        ],
        
        'paypal' => [
            'enabled' => env('PAYMENT_PAYPAL_ENABLED', false),
            'name' => env('PAYMENT_PAYPAL_NAME', 'PayPal'),
            'note' => env('PAYMENT_PAYPAL_NOTE', 'PayPal NOTE'),
            'description' => env('PAYMENT_PAYPAL_DESCRIPTION', 'Pay with your PayPal account'),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'sandbox_client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
            'sandbox_client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
            'live_client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
            'live_client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'icon' => env('PAYMENT_PAYPAL_ICON', '🌐'),
            'color' => env('PAYMENT_PAYPAL_COLOR', '#003087'),
            'supported_methods' => explode(',', env('PAYMENT_PAYPAL_METHODS', 'paypal')),
            'min_amount' => env('PAYMENT_PAYPAL_MIN_AMOUNT', 1),
            'max_amount' => env('PAYMENT_PAYPAL_MAX_AMOUNT', 100000),
            'currency' => env('PAYMENT_PAYPAL_CURRENCY', 'EGP'),
            'Instructions' => explode(',', env('PAYMENT_PAYPAL_INSTRUCTIONS', 'اتبع التعليمات على الشاشة')),
        ],
    ],
    
    'currencies' => [
        'default' => env('PAYMENT_DEFAULT_CURRENCY', 'EGP'),
        'supported' => explode(',', env('PAYMENT_SUPPORTED_CURRENCIES', 'EGP,USD,EUR')),
        'exchange_rates' => [
            'EGP' => 1,
            'USD' => env('EXCHANGE_RATE_USD', 46.90),
            'EUR' => env('EXCHANGE_RATE_EUR', 50),
        ],
    ],
    
    'urls' => [
        'return_url' => env('PAYMENT_RETURN_URL', env('APP_URL') . '/payment/return'),
        'cancel_url' => env('PAYMENT_CANCEL_URL', env('APP_URL') . '/payment/cancel'),
        'webhook_url' => env('PAYMENT_WEBHOOK_URL', env('APP_URL') . '/api/payments/webhook/{gateway}'),
    ],
    
    'settings' => [
        'auto_capture' => env('PAYMENT_AUTO_CAPTURE', true),
        'allow_partial_payments' => env('PAYMENT_ALLOW_PARTIAL', false),
        'save_payment_method' => env('PAYMENT_SAVE_METHOD', false),
        'invoice_prefix' => env('PAYMENT_INVOICE_PREFIX', 'INV-'),
        'tax_percentage' => env('TAX_PERCENTAGE', 15),
        'shipping_fee' => env('SHIPPING_FEE', 25),
    ],
    
    'simulation' => [
        'enabled' => env('PAYMENT_SIMULATION_ENABLED', true),
        'log_transactions' => env('PAYMENT_SIMULATION_LOG', true),
        'default_success_rate' => env('PAYMENT_SIMULATION_SUCCESS_RATE', 95),
        'simulate_delay' => env('PAYMENT_SIMULATION_DELAY', true),
        'delay_min' => env('PAYMENT_SIMULATION_DELAY_MIN', 1),
        'delay_max' => env('PAYMENT_SIMULATION_DELAY_MAX', 3),
    ],
];