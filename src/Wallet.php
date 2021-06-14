<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet;

use Larva\Wallet\Models\Recharge;
use Larva\Wallet\Models\Withdrawals;

/**
 * 钱包快捷操作
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Wallet
{
    /**
     * 创建充值请求
     * @param int|string $user_id 用户ID
     * @param string $channel 渠道
     * @param int $amount 金额 单位分
     * @param string $type 支付类型
     * @param string|null $clientIP 客户端IP
     * @return Recharge
     */
    public static function recharge($user_id, string $channel, int $amount, string $type, string $clientIP = null): Recharge
    {
        return Recharge::create(['user_id' => $user_id, 'channel' => $channel, 'amount' => $amount, 'type' => $type, 'client_ip' => $clientIP]);
    }
}