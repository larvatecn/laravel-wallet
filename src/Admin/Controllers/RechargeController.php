<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Wallet\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Grid;
use Larva\Wallet\Models\Recharge;

/**
 * 余额充值
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeController extends AdminController
{
    /**
     * Get content title.
     *
     * @return string
     */
    protected function title(): string
    {
        return '余额充值';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(new Recharge(), function (Grid $grid) {
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('status', '状态')->select(Recharge::getStatusLabels());
                //顶部筛选
                $filter->scope('pending', '待付款')->where('status', Recharge::STATUS_PENDING);
                $filter->scope('succeeded', '充值成功')->where('status', Recharge::STATUS_SUCCEEDED);
                $filter->scope('failure', '充值失败')->where('status', Recharge::STATUS_FAILED);
            });
            $grid->quickSearch(['id']);
            $grid->model()->orderBy('id', 'desc');

            $grid->column('id', 'ID')->sortable();
            $grid->column('user_id', '用户ID');
            $grid->column('channel', '付款渠道');
            $grid->column('type', '付款类型');
            $grid->column('amount', '充值金额')->display(function ($amount) {
                return ($amount / 100) . '元';
            });
            $grid->column('status', '状态')->using(Recharge::getStatusLabels())->dot(Recharge::getStatusDots(), 'info');
            $grid->column('client_ip', '客户端IP');
            $grid->column('succeeded_at', '成功时间');

            $grid->column('created_at', '创建时间')->sortable();

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
        });
    }
}