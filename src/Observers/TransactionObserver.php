<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Wallet\Observers;

use Illuminate\Support\Facades\DB;
use Larva\Wallet\Exceptions\WalletException;
use Larva\Wallet\Models\Transaction;

/**
 * 交易模型观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TransactionObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Transaction $transaction
     * @return void
     * @throws WalletException
     */
    public function created(Transaction $transaction)
    {
        //开始事务
        DB::beginTransaction();
        try {
            $user = $transaction->user()->lockForUpdate()->first();
            $user->updateQuietly(['available_amount' => $transaction->available_amount]);
            DB::commit();
        } catch (\Exception $e) {//回滚事务
            DB::rollback();
            throw new WalletException($e->getMessage(), 500);
        }
    }
}
