<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * 钱包服务提供者
 * @package Larva\Wallet
 */
class WalletServiceProvider  extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang'),
            ], 'wallet-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'wallet');

        // Transaction
        Event::listen(\Larva\Transaction\Events\ChargeClosed::class, \Larva\Wallet\Listeners\ChargeClosedListener::class);//支付关闭
        Event::listen(\Larva\Transaction\Events\ChargeFailure::class, \Larva\Wallet\Listeners\ChargeFailureListener::class);//支付失败
        Event::listen(\Larva\Transaction\Events\ChargeShipped::class, \Larva\Wallet\Listeners\ChargeShippedListener::class);//支付成功
        Event::listen(\Larva\Transaction\Events\TransferFailure::class, \Larva\Wallet\Listeners\TransferFailureListener::class);//提现失败
        Event::listen(\Larva\Transaction\Events\TransferShipped::class, \Larva\Wallet\Listeners\TransferShippedListener::class);//提现成功

        // Observers
        \Larva\Wallet\Models\Recharge::observe(\Larva\Wallet\Observers\RechargeObserver::class);
        \Larva\Wallet\Models\Transaction::observe(\Larva\Wallet\Observers\TransactionObserver::class);
        \Larva\Wallet\Models\Withdrawals::observe(\Larva\Wallet\Observers\WithdrawalsObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
