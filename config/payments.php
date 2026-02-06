<?php

return [
	
	'default' => env('PAYMENT_DEFAULT_GATEWAY', 'test'),
	
	'gateways' => [
		'test' => [
			'enabled' => env('PAYMENT_TEST_ENABLED', true),
			'name' => env('PAYMENT_TEST_NAME', 'Test Gateway'),
			'description' => env('PAYMENT_TEST_DESCRIPTION', 'Gateway for testing purposes'),
			'mode' => env('PAYMENT_TEST_MODE', 'sandbox'),
			'icon' => env('PAYMENT_TEST_ICON', '🧪'),
			'color' => env('PAYMENT_TEST_COLOR', '#6c757d'),
			'supported_methods' => explode(',', env('PAYMENT_TEST_METHODS', 'credit_card,test_card')),
			'min_amount' => env('PAYMENT_TEST_MIN_AMOUNT', 1),
			'max_amount' => env('PAYMENT_TEST_MAX_AMOUNT', 10000),
		],
		
		'stripe' => [
			'enabled' => env('PAYMENT_STRIPE_ENABLED', true),
			'name' => env('PAYMENT_STRIPE_NAME', 'Stripe'),
			'description' => env('PAYMENT_STRIPE_DESCRIPTION', 'Secure credit card payments'),
			'secret_key' => env('STRIPE_SECRET_KEY'),
			'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
			'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
			'mode' => env('STRIPE_MODE', 'sandbox'),
			'icon' => env('PAYMENT_STRIPE_ICON', '💳'),
			'color' => env('PAYMENT_STRIPE_COLOR', '#6772e5'),
			'supported_methods' => explode(',', env('PAYMENT_STRIPE_METHODS', 'credit_card,apple_pay,google_pay')),
			'min_amount' => env('PAYMENT_STRIPE_MIN_AMOUNT', 1),
			'max_amount' => env('PAYMENT_STRIPE_MAX_AMOUNT', 50000),
		],
	],
    
    'currencies' => [
        'default' => env('PAYMENT_DEFAULT_CURRENCY', 'EGP'),
        'supported' => explode(',', env('PAYMENT_SUPPORTED_CURRENCIES', 'EGP,USD,EUR')),
    ],
    
    'urls' => [
        'return_url' => env('PAYMENT_RETURN_URL', env('APP_URL') . '/api/payment/return'),
        'cancel_url' => env('PAYMENT_CANCEL_URL', env('APP_URL') . '/api/payment/cancel'),
        'webhook_url' => env('PAYMENT_WEBHOOK_URL', env('APP_URL') . '/api/payments/webhook/{gateway}'),
    ],
];