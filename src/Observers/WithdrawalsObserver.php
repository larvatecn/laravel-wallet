<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types=1);

namespace Larva\Wallet\Observers;

use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
            //冻结提现金额
            $change_freeze_amount = -$withdrawals->amount;
            $withdrawals->transaction()->create([
                'user_id' => $withdrawals->user_id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'description' => trans('wallet.withdrawal_balance'),
                'amount' => $change_freeze_amount,
                'available_amount' => $withdrawals->user->available_amount + $change_freeze_amount,
                'client_ip' => $withdrawals->client_ip,
            ]);
            //创建转账请求
            $withdrawals->transfer()->create([
                'amount' => $withdrawals->amount,
                'currency' => 'CNY',
                'description' => trans('wallet.withdrawal_balance'),
                'channel' => $withdrawals->channel,
                'metadata' => $withdrawals->metadata,
                'recipient_id' => $withdrawals->recipient,
                'extra' => $withdrawals->extra,
            ]);
            DB::commit();
        } catch (\Exception $e) {//回滚事务
            DB::rollback();
            throw new WalletException($e->getMessage(), 500);
        }
    }
}
