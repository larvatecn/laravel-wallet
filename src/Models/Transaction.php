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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 钱包交易明细
 *
 * @property string $id ID
 * @property int $user_id 用户ID
 * @property int $amount 交易金额
 * @property int $available_amount 交易后可用金额
 * @property string $description 描述
 * @property string $type 类型
 * @property-read string $typeName
 * @property string $client_ip 客户端IP
 * @property \App\Models\User $user
 * @property Carbon|null $created_at 交易时间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Transaction extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'wallet_transactions';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'available_amount', 'description', 'source', 'type', 'client_ip'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    const UPDATED_AT = null;

    const TYPE_RECHARGE = 'recharge';//充值
    const TYPE_RECHARGE_REFUND = 'recharge_refund';//充值退款
    const TYPE_RECHARGE_REFUND_FAILED = 'recharge_refund_failed';//充值退款失败
    const TYPE_WITHDRAWAL = 'withdrawal';//提现申请
    const TYPE_WITHDRAWAL_FAILED = 'withdrawal_failed';//提现失败
    const TYPE_WITHDRAWAL_REVOKED = 'withdrawal_revoked';//提现撤销
    const TYPE_PAYMENT = 'payment';//支付/收款
    const TYPE_PAYMENT_REFUND = 'payment_refund';//退款/收到退款
    const TYPE_TRANSFER = 'transfer';//转账/收到转账
    const TYPE_RECEIPTS_EXTRA = 'receipts_extra';//赠送
    const TYPE_ROYALTY = 'royalty';//分润/收到分润
    const TYPE_REWARD = 'reward';//奖励/收到奖励

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
     * 获取所有操作类型
     * @return array
     */
    public static function getAllType(): array
    {
        return [
            static::TYPE_RECHARGE => trans('wallet.' . static::TYPE_RECHARGE),
            static::TYPE_RECHARGE_REFUND => trans('wallet.' . static::TYPE_RECHARGE_REFUND),
            static::TYPE_RECHARGE_REFUND_FAILED => trans('wallet.' . static::TYPE_RECHARGE_REFUND_FAILED),
            static::TYPE_WITHDRAWAL => trans('wallet.' . static::TYPE_WITHDRAWAL),
            static::TYPE_WITHDRAWAL_FAILED => trans('wallet.' . static::TYPE_WITHDRAWAL_FAILED),
            static::TYPE_WITHDRAWAL_REVOKED => trans('wallet.' . static::TYPE_WITHDRAWAL_REVOKED),
            static::TYPE_PAYMENT => trans('wallet.' . static::TYPE_PAYMENT),
            static::TYPE_PAYMENT_REFUND => trans('wallet.' . static::TYPE_PAYMENT_REFUND),
            static::TYPE_TRANSFER => trans('wwallet.' . static::TYPE_TRANSFER),
            static::TYPE_RECEIPTS_EXTRA => trans('wallet.' . static::TYPE_RECEIPTS_EXTRA),
            static::TYPE_ROYALTY => trans('wallet.' . static::TYPE_ROYALTY),
            static::TYPE_REWARD => trans('wallet.' . static::TYPE_REWARD),
        ];
    }

    /**
     * 获取Type名称
     * @return string
     */
    public function getTypeNameAttribute(): string
    {
        return trans('wallet.' . $this->type);
    }

    /**
     * Get the source entity that the Transaction belongs to.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
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
}
