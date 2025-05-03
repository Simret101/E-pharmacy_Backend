<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Omnipay\Omnipay;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('omnipay.paypal', function ($app) {
            $gateway = Omnipay::create('PayPal_Rest');
            
            $gateway->setClientId(config('services.paypal.client_id'));
            $gateway->setSecret(config('services.paypal.secret'));
            $gateway->setTestMode(config('services.paypal.mode') === 'sandbox');

            return $gateway;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 