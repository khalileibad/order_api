<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Payments\PaymentManager;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
	public function register(): void
	{
		$this->app->singleton('payment', function ($app) {
			return new PaymentManager($app);
		});
		
	}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
		if (file_exists(config_path('payments.php'))) {
			$this->mergeConfigFrom(
				config_path('payments.php'), 'payments'
			);
		}
		
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../../config/payments.php' => config_path('payments.php'),
			], 'payments-config');
		}
	}
	
}

