<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Larva\Wallet\Exceptions\WalletException;

/**
 * 钱包
 * @property int $user_id
 * @property int $available_amount 可用金额
 * @property \Illuminate\Support\Carbon|null $created_at 钱包创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 钱包更新时间
 *
 * @property \Illuminate\Foundation\Auth\User $user
 * @property Recharge[] $recharges 钱包充值记录
 * @property Transaction[] $transactions 钱包交易记录
 * @property Withdrawals[] $withdrawals 钱包提现记录
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet userId($user_id)
 */
class Wallet extends Model
{
    /**
     * @var string 主键字段名
     */
    protected $primaryKey = 'user_id';

    /**
     * @var bool 关闭自增
     */
    public $incrementing = false;

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'wallets';

    /**
     * 该模型是否被自动维护时间戳.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'available_amount'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'available_amount' => 0,
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
     * 获取指定用户钱包
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId($query, $user_id)
    {
        return $query->where('user_id', '=', $user_id);
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.' . config('auth.guards.web.provider') . '.model'));
    }

    /**
     * 钱包充值明细
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function recharges()
    {
        return $this->hasMany(Recharge::class, 'user_id', 'user_id');
    }

    /**
     * 钱包交易明细
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'user_id');
    }

    /**
     * 钱包提现明细
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawals::class, 'user_id', 'user_id');
    }

    /**
     * 创建充值请求
     * @param string $channel 渠道
     * @param int $amount 金额 单位分
     * @param string $type 支付类型
     * @param string $clientIP 客户端IP
     * @return Model|Recharge
     */
    public function rechargeAction($channel, $amount, $type, $clientIP = null)
    {
        return $this->recharges()->create(['channel' => $channel, 'amount' => $amount, 'type' => $type, 'client_ip' => $clientIP]);
    }

    /**
     * 创建提现请求
     * @param int $amount
     * @param string $channel
     * @param string $recipient 收款账户
     * @param array $metaData 附加信息
     * @param string $clientIP 客户端IP
     * @return Model|Withdrawals
     * @throws WalletException
     */
    public function withdrawalsAction($amount, $channel, $recipient, $metaData = [], $clientIP = null)
    {
        $availableAmount = bcsub($this->available_amount, $amount);
        if ($availableAmount < 0) {//计算后如果余额小于0，那么结果不合法。
            throw new WalletException('Insufficient wallet balance.');//钱包余额不足
        }
        return $this->withdrawals()->create([
            'amount' => $amount,
            'channel' => $channel,
            'status' => Withdrawals::STATUS_PENDING,
            'recipient' => $recipient,
            'metadata' => $metaData,
            'client_ip' => $clientIP,
        ]);
    }
}
