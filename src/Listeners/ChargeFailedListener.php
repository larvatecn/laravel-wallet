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
use Larva\Transaction\Events\ChargeFailed;

/**
 * 付款失败事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ChargeFailedListener implements ShouldQueue
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
     * @param ChargeFailed $event
     * @return void
     */
    public function handle(ChargeFailed $event)
    {
        if ($event->charge->order instanceof Recharge) {//充值失败
            $event->charge->order->markFailed();
        }
    }
}
