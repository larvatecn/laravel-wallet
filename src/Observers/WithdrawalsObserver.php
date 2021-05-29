<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet\Observers;

use Larva\Wallet\Exceptions\WalletException;
use Larva\Wallet\Models\Transaction;
use Larva\Wallet\Models\Withdrawals;

/**
 * 提现模型观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalsObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Withdrawals $withdrawals
     * @return void
     * @throws WalletException
     */
    public function created(Withdrawals $withdrawals)
    {
        //开始事务
        $dbConnection = $withdrawals::onWriteConnection()->getConnection();
        $dbConnection->beginTransaction();
        try {
            //冻结变动金额，为负数
            $change_freeze_amount = -$withdrawals->amount;
            $withdrawals->transaction()->create([
                'user_id' => $withdrawals->user_id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'description' => trans('wallet.withdrawal_balance'),
                'amount' => $change_freeze_amount,
                'available_amount' => bcadd($withdrawals->wallet->available_amount, $change_freeze_amount),
                'client_ip' => $withdrawals->client_ip,
            ]);
            $withdrawals->transfer()->create([
                'amount' => $withdrawals->amount,
                'currency' => 'CNY',
                'description' => trans('wallet.withdrawal_balance'),
                'channel' => $withdrawals->channel,
                'metadata' => $withdrawals->metadata,
                'recipient_id' => $withdrawals->recipient,
                'extra' => $withdrawals->extra,
            ]);
            $dbConnection->commit();
        } catch (\Exception $e) {//回滚事务
            $dbConnection->rollback();
            throw new WalletException($e->getMessage(), 500);
        }
    }
}
