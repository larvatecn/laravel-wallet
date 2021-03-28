<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 结算账户表
 * @property int $id ID序号
 * @property int $user_id 用户ID
 * @property string $channel 结算账号渠道名称
 * @property-read string $account 结算账号
 * @property-read string $name 结算账号开户名
 * @property-read string $type 结算账号类型
 * @property array $recipient 脱敏的结算账号接收者信息
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property \Illuminate\Foundation\Auth\User $user
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SettleAccount extends Model
{
    use SoftDeletes;

    const CHANNEL_WECHAT = 'weixin';
    const CHANNEL_WECHAT_MINI_PROGRAM = 'weixin-mini-program';
    const CHANNEL_WECHAT_WEB = 'weixin-web';
    const CHANNEL_WECHAT_MOBILE = 'weixin-mobile';
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_BANK_ACCOUNT = 'bank_account';

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'wallet_settle_accounts';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'channel', 'recipient'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * 这个属性应该被转换为原生类型.
     *
     * @var array
     */
    protected $casts = [
        'recipient' => 'array',
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
     * Get the user relation.
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
     * 获取提现账户
     * @return string
     */
    public function getAccountAttribute()
    {
        return $this->recipient['account'] ?? '';
    }

    /**
     * 获取提现账户开户名
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->recipient['name'] ?? '';
    }

    /**
     * 获取提现账户类型
     * @return mixed|string
     */
    public function getTypeAttribute()
    {
        return $this->recipient['type'] ?? '';
    }

    /**
     * 查询支付宝
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAlipay($query)
    {
        return $query->where('channel', static::CHANNEL_ALIPAY);
    }

    /**
     * 链接用户
     * @param User $user
     * @return bool
     */
    public function connect($user)
    {
        return $this->update(['user_id' => $user->id]);
    }

    /**
     * 设置支付账户信息
     * @param array $recipient
     */
    public function setRecipient($recipient)
    {
        $recipient['type'] = $recipient['type'] ?? 'b2c';
        if ($this->channel == static::CHANNEL_ALIPAY) {
            $recipient['account_type'] = $recipient['accountType'] ?? 'ALIPAY_LOGONID';
        } else if ($this->channel == static::CHANNEL_WECHAT || $this->channel == static::CHANNEL_WECHAT_WEB || $this->channel == static::CHANNEL_WECHAT_MOBILE || $this->channel == static::CHANNEL_WECHAT_MINI_PROGRAM) {
            $recipient['force_check'] = $recipient['forceCheck'] ?? true;
        } else if ($this->channel == static::CHANNEL_BANK_ACCOUNT) {
            $recipient['card_type'] = $recipient['card_type'] ?? 0;
        }
        $this->recipient = $recipient;
    }

    /**
     * 设置支付宝账户
     * @param string $account 接收者支付宝账号。
     * @param string $name 接收者姓名
     * @param string $type 账户类型，分为两种：b2c：个人,b2b：企业。不传时默认为b2c类型。
     * @param string $accountType 账户类型 ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号，以 2088 开头的 16 位纯数字组成；ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
     */
    public function setAlipayRecipient($account, $name, $type = 'b2c', $accountType = 'ALIPAY_LOGONID')
    {
        $this->channel = static::CHANNEL_ALIPAY;
        $this->recipient = [
            'account' => $account,
            'name' => $name,
            'type' => $type,
            'account_type' => $accountType
        ];
    }

    /**
     * 设置微信账户
     * @param string $account 微信的OpenID
     * @param string $name 微信实名的姓名
     * @param string $type 账户类型，分为两种：b2c：个人,b2b：企业。不传时默认为b2c类型。
     * @param boolean $forceCheck 是否强制校验收款人姓名。仅当 name 参数不为空时该参数生效。
     */
    public function setWechatRecipient($account, $name, $type = 'b2c', $forceCheck = true)
    {
        $this->channel = static::CHANNEL_WECHAT_WEB;
        $this->recipient = [
            'account' => $account,
            'name' => $name,
            'type' => $type,
            'force_check' => $forceCheck,
        ];
    }

    /**
     * 设置银行卡
     * @param string $account 接收者银行账号/卡号。
     * @param string $name 接收者银行开户名。
     * @param string $type 转账类型。b2c：企业向个人付款，b2b：企业向企业付款。
     * @param string $openBankCode 开户银行编号（针对 chanpay / allinpay / unionpay 渠道使用），请根据渠道的不同参考 银联电子代付银行编号说明 、通联代付银行编号说明 或 畅捷代付银行编号说明。
     * @param string $openBank 开户银行名称（针对 unionpay 渠道使用）。
     * @param string $cardType 银行卡号类型，0：银行卡；1：存折；2：信用卡；3：准贷记卡；4：其他。（jdpay 不支持 1。chanpay 不支持 1、3、4）
     * @param string $subBank 开户支行名称，1~80位（针对 allinpay / unionpay 渠道使用）。若使用 allinpay 渠道且 type 为 b2b，则此参数必填，详情请下载 支付行号 。
     * @param string $subBankCode 支付行号（仅针对 allinpay 渠道使用），1~12位，且在 type 为 b2b 时此参数必填，详情请下载 支付行号 。
     * @param string $prov 开户银行所在省份，（针对 allinpay / unionpay / chanpay 渠道使用）。若使用 allinpay、chanpay 渠道且 type 为 b2b，则此参数必填。allinpay渠道此参数要求：不带 “省” 或 “自治区”，需填写成：广东、广西、内蒙古等，详情请参考 中国邮政区号表 内的「省洲名称」列的内容填写。chanpay渠道此参数要求：参考 畅捷代付省市列表 内的「prov」列的内容填写。
     * @param string $city 开户银行所在城市，（针对 allinpay / unionpay / chanpay 渠道使用）。若使用 allinpay、chanpay 渠道且 type 为 b2b，则此参数必填。allinpay渠道此参数要求：不带 “市”，需填写成：广州、南宁等。如果是直辖市，则填区，如北京（市）朝阳（区），详情请参考 中国邮政区号表 内的「地区名称」列的内容填写。chanpay渠道此参数要求：参考 畅捷代付省市列表 内的「city」列的内容填写。
     */
    public function setBankRecipient($account, $name, $type, $openBankCode, $openBank = null, $cardType = null, $subBank = null, $subBankCode = null, $prov = null, $city = null)
    {
        $this->recipient = [
            'account' => $account,
            'name' => $name,
            'type' => $type,
            'open_bank_code' => $openBankCode,
            'open_bank' => $openBank,
            'card_type' => $cardType,
            'sub_bank' => $subBank,
            'sub_bank_code' => $subBankCode,
            'prov' => $prov,
            'city' => $city
        ];
    }
}
