<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Larva\Wallet\Models\Recharge;

/**
 * 充值成功通知
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeSucceeded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The user.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    public $user;

    /**
     * @var Recharge
     */
    public $recharge;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param Recharge $recharge
     */
    public function __construct($user,Recharge $recharge)
    {
        $this->user= $user;
        $this->recharge = $recharge;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Wallet recharge succeeded'))
            ->line(Lang::get('Your recharge amount is :amount', ['amount' => $this->recharge->transaction->amount/100]))
            ->line(Lang::get('Thank you for choosing, we will be happy to help you in the process of your subsequent use of the service.'));
    }
}
