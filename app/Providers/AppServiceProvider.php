<?php

namespace App\Providers;

use App\Services\Payment\Contracts\PaymentInterface;
use App\Services\Payment\Providers\Zarinpal\Zarinpal;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentInterface::class, Zarinpal::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
