<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Wallet\Observers;

use Larva\Wallet\Models\Recharge;

/**
 * 充值模型观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Recharge $recharge
     * @return void
     * @throws \Yansongda\Pay\Exceptions\InvalidGatewayException
     */
    public function created(Recharge $recharge)
    {
        $recharge->charge()->create([
            'user_id' => $recharge->user_id,
            'amount' => $recharge->amount,
            'channel' => $recharge->channel,
            'subject' => trans('wallet.Wallet_recharge'),
            'body' => trans('wallet.Wallet_recharge'),
            'client_ip' => $recharge->client_ip,
            'type' => $recharge->type,//交易类型
        ]);
    }
}
