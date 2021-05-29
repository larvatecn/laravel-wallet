<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Wallet\Events;

use Illuminate\Queue\SerializesModels;
use Larva\Wallet\Models\Recharge;

/**
 * 交易已支付
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeShipped
{
    use SerializesModels;

    /**
     * @var Recharge
     */
    public $recharge;

    /**
     * ChargeFailure constructor.
     * @param Recharge $recharge
     */
    public function __construct(Recharge $recharge)
    {
        $this->recharge = $recharge;
    }
}