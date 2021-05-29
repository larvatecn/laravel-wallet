<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Larva\Transaction\Models\Charge;

/**
 * 钱包充值明细
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property string $channel
 * @property string $type
 * @property string $status
 * @property string $client_ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $succeeded_at
 *
 * @property Charge $charge
 * @property \Illuminate\Foundation\Auth\User $user
 * @property Transaction $transaction
 * @property Wallet $wallet
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Recharge extends Model
{

    const STATUS_PENDING = 'pending';//处理中： pending
    const STATUS_SUCCEEDED = 'succeeded';//完成： succeeded
    const STATUS_FAILED = 'failed';//失败： failed

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'wallet_recharges';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'channel', 'type', 'status', 'client_ip', 'succeeded_at'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'succeeded_at'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'user_id', 'user_id');
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.' . config('auth.guards.web.provider') . '.model')
        );
    }

    /**
     * Get the entity's transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the entity's charge.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function charge()
    {
        return $this->morphOne(Charge::class, 'order');
    }

    /**
     * 设置交易成功
     */
    public function setSucceeded()
    {
        $this->update(['channel' => $this->charge->channel, 'type' => $this->charge->type, 'status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_RECHARGE,
            'description' => trans('wallet.wallet_recharge'),
            'amount' => $this->amount,
            'available_amount' => bcadd($this->wallet->available_amount, $this->amount)
        ]);
        event(new \Larva\Wallet\Events\RechargeShipped($this));
        $this->user->notify(new \Larva\Wallet\Notifications\RechargeSucceeded($this->user, $this));
    }

    /**
     * 设置交易失败
     */
    public function setFailure()
    {
        $this->update(['status' => static::STATUS_FAILED]);
        event(new \Larva\Wallet\Events\RechargeFailure($this));
    }

    /**
     * 状态
     * @return string[]
     */
    public static function getStatusLabels()
    {
        return [
            static::STATUS_PENDING => '等待付款',
            static::STATUS_SUCCEEDED => '充值成功',
            static::STATUS_FAILED => '充值失败',
        ];
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots()
    {
        return [
            static::STATUS_PENDING => 'info',
            static::STATUS_SUCCEEDED => 'success',
            static::STATUS_FAILED => 'warning',
        ];
    }
}
