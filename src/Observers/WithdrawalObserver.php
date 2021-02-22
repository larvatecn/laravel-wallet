<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Wallet\Observers;

use Larva\Wallet\Exceptions\WalletException;
use Larva\Wallet\Models\Transaction;
use Larva\Wallet\Models\Wallet;
use Larva\Wallet\Models\Withdrawal;

/**
 * 提现模型观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Withdrawal $withdrawal
     * @return void
     * @throws WalletException
     */
    public function created(Withdrawal $withdrawal)
    {
        //开始事务
        $dbConnection = $withdrawal::onWriteConnection()->getConnection();
        $dbConnection->beginTransaction();
        try {
            //冻结变动金额，为负数
            $change_freeze_amount = -$withdrawal->amount;
            $withdrawal->transaction()->create([
                'user_id' => $withdrawal->user_id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'description' => trans('wallet.withdrawal_balance'),
                'amount' => $change_freeze_amount,
                'available_amount' => bcadd($withdrawal->wallet->available_amount, $change_freeze_amount),
                'client_ip' => $withdrawal->client_ip,
            ]);
            $withdrawal->transfer()->create([
                'amount' => $withdrawal->amount,
                'currency' => 'CNY',
                'description' => trans('wallet.withdrawal_balance'),
                'channel' => $withdrawal->channel,
                'metadata' => $withdrawal->metadata,
                'recipient_id' => $withdrawal->recipient,
                'extra' => $withdrawal->extra,
            ]);
            $dbConnection->commit();
        } catch (\Exception $e) {//回滚事务
            $dbConnection->rollback();
            throw new WalletException($e->getMessage(), 500);
        }
    }
}
