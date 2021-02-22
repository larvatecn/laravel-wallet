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
        $dbConnection = Wallet::onWriteConnection()->getConnection();
        $dbConnection->beginTransaction();
        try {
            $wallet = Wallet::query()->where('user_id', '=', $transaction->user_id)->lockForUpdate()->first();
            $wallet->update(['available_amount' => $transaction->available_amount]);//更新用户余额
            $dbConnection->commit();
        } catch (\Exception $e) {//回滚事务
            $dbConnection->rollback();
            throw new WalletException($e->getMessage(), 500);
        }
    }
}
