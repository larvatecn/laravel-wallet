<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Wallet\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larva\Wallet\Models\Withdrawals;
use Larva\Transaction\Events\TransferShipped;

/**
 * 提现成功事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TransferShippedListener implements ShouldQueue
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
     * @param TransferShipped $event
     * @return void
     */
    public function handle(TransferShipped $event)
    {
        if ($event->transfer->order instanceof Withdrawals) {
            $event->transfer->order->setSucceeded();
        }
    }
}
