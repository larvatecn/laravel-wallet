<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types=1);

namespace Larva\Wallet\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Larva\Transaction\Models\Charge;
use Larva\Wallet\Events\RechargeFailure;
use Larva\Wallet\Events\RechargeShipped;

/**
 * 钱包充值明细
 *
 * @property int $id ID
 * @property int $user_id 用户ID
 * @property int $amount 充值金额，单位分
 * @property string $channel 支付渠道
 * @property string $trade_type 支付类型
 * @property string $status 状态
 * @property string $client_ip 客户端IP
 * @property Carbon $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $succeeded_at 成功时间
 *
 * @property Charge $charge
 * @property \App\Models\User $user
 * @property Transaction $transaction
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
        'user_id', 'amount', 'channel', 'trade_type', 'status', 'client_ip', 'succeeded_at'
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
        'status' => self::STATUS_PENDING,
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.' . config('auth.guards.web.provider') . '.model'));
    }

    /**
     * Get the entity's transaction.
     *
     * @return MorphOne
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the entity's charge.
     *
     * @return MorphOne
     */
    public function charge(): MorphOne
    {
        return $this->morphOne(Charge::class, 'order');
    }

    /**
     * 设置交易成功
     */
    public function markSucceeded()
    {
        $this->update(['channel' => $this->charge->channel, 'trade_type' => $this->charge->trade_type, 'status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_RECHARGE,
            'description' => trans('wallet.wallet_recharge'),
            'amount' => $this->amount,
            'available_amount' => $this->user->available_amount + $this->amount
        ]);
        Event::dispatch(new RechargeShipped($this));
        $this->user->notify(new \Larva\Wallet\Notifications\RechargeSucceeded($this->user, $this));
    }

    /**
     * 设置交易失败
     */
    public function markFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
        Event::dispatch(new RechargeFailure($this));
    }

    /**
     * 状态
     * @return string[]
     */
    public static function getStatusLabels(): array
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
    public static function getStatusDots(): array
    {
        return [
            static::STATUS_PENDING => 'info',
            static::STATUS_SUCCEEDED => 'success',
            static::STATUS_FAILED => 'warning',
        ];
    }
}
