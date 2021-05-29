<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Wallet\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larva\Wallet\Models\Recharge;
use Larva\Transaction\Events\ChargeShipped;

/**
 * 付款成功事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ChargeShippedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChargeShipped $event
     * @return void
     */
    public function handle(ChargeShipped $event)
    {
        if ($event->charge->order instanceof Recharge) {//充值成功
            $event->charge->order->setSucceeded();
        }
    }
}
