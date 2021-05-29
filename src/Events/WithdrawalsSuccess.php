<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Wallet\Events;

use Illuminate\Queue\SerializesModels;
use Larva\Wallet\Models\Withdrawals;

/**
 * 提现成功事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalsSuccess
{
    use SerializesModels;

    /**
     * @var Withdrawals
     */
    public $withdrawals;

    /**
     * RefundFailure constructor.
     * @param Withdrawals $withdrawals
     */
    public function __construct(Withdrawals $withdrawals)
    {
        $this->withdrawals = $withdrawals;
    }
}