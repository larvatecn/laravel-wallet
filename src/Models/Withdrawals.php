<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Larva\Transaction\Models\Transfer;

/**
 * 钱包提现明细
 *
 * @property int $user_id 用户ID
 * @property int $amount 金额
 * @property string $status 状态
 * @property string $channel 渠道
 * @property string $recipient 支付凭证
 * @property string $client_ip 客户端IP
 * @property array $metadata
 * @property-read array $extra 提现附加参数
 * @property Carbon|null $created_at
 * @property Carbon|null $canceled_at
 * @property Carbon|null $succeeded_at
 *
 * @property \Illuminate\Foundation\Auth\User $user
 * @property Wallet $wallet
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Withdrawals extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'wallet_withdrawals';

    const STATUS_PENDING = 'pending';//处理中： pending
    const STATUS_SUCCEEDED = 'succeeded';//完成： succeeded
    const STATUS_FAILED = 'failed';//失败： failed
    const STATUS_CANCELED = 'canceled';//取消： canceled

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'status', 'channel', 'recipient','client_ip',  'metadata', 'canceled_at', 'succeeded_at'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'canceled_at',
        'succeeded_at'
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
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
     * 获取提现附加参数
     * @return array
     */
    public function getExtraAttribute()
    {
        return [
            //微信
            'type' => $this->metadata['type'] ?? '',
            'user_name' => $this->metadata['name'] ?? '',
            //支付宝
            'recipient_name' => $this->metadata['name'] ?? '',
            'recipient_account_type' => $this->metadata['account_type'] ?? ''
        ];
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'user_id', 'user_id');
    }

    /**
     * Get the entity's transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the entity's transfer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function transfer()
    {
        return $this->morphOne(Transfer::class, 'order');
    }

    /**
     * 设置提现成功
     */
    public function setSucceeded()
    {
        $this->update(['status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);
        event(new \Larva\Wallet\Events\WithdrawalsSuccess($this));
    }

    /**
     * 取消提现
     * @return bool
     */
    public function setCanceled()
    {
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL_REVOKED,
            'description' => trans('wallet.withdrawal_revoked'),
            'amount' => $this->amount,
            'available_amount' => bcadd($this->wallet->available_amount, $this->amount)
        ]);
        $this->update(['status' => static::STATUS_CANCELED, 'canceled_at' => $this->freshTimestamp()]);
        event(new \Larva\Wallet\Events\WithdrawalsCanceled($this));
        return true;
    }

    /**
     * 提现失败平账
     * @return bool
     */
    public function setFailed()
    {
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL_FAILED,
            'description' => trans('wallet.withdrawal_failed'),
            'amount' => $this->amount,
            'available_amount' => bcadd($this->wallet->available_amount, $this->amount)
        ]);
        $this->update(['status' => static::STATUS_FAILED, 'canceled_at' => $this->freshTimestamp()]);
        event(new \Larva\Wallet\Events\WithdrawalsFailure($this));
        return true;
    }

    /**
     * 状态
     * @return string[]
     */
    public static function getStatusLabels()
    {
        return [
            static::STATUS_PENDING => '等待处理',
            static::STATUS_SUCCEEDED => '提现成功',
            static::STATUS_FAILED => '提现失败',
            static::STATUS_CANCELED => '提现撤销',
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
            static::STATUS_CANCELED => 'info',
        ];
    }
}
