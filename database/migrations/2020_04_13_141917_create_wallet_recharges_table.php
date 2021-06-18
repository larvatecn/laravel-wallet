<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletRechargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_recharges', function (Blueprint $table) {
            $table->increments('id')->comment('充值 id');
            $table->unsignedBigInteger('user_id')->comment('用户 id');
            $table->string('channel', 10)->comment('充值渠道名称');
            $table->string('trade_type', 10)->comment('充值渠道类型');
            $table->unsignedInteger('amount')->default(0)->comment('充值金额');//单位分
            $table->ipAddress('client_ip')->nullable()->comment('用户IP');//发起支付请求客户端的 IP 地址
            $table->string('status', 10)->default(\Larva\Wallet\Models\Recharge::STATUS_PENDING)->comment('状态');
            $table->timestamp('succeeded_at')->nullable()->comment('充值成功时间');//成功时间
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_recharges');
    }
}
