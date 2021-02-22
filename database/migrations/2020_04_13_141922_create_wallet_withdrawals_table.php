<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('提现 id');
            $table->unsignedBigInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('amount')->default(0)->comment('提现金额');//单位：分
            $table->string('status', 10)->default('created')->comment('提现状态');
            $table->string('channel', 30)->comment('提现渠道');
            $table->string('recipient');
            $table->text('metadata')->nullable();
            $table->ipAddress('client_ip')->nullable()->comment('提现IP');//发起支付请求客户端的 IP 地址
            $table->timestamps();
            $table->timestamp('canceled_at', 0)->nullable()->comment('提现取消时间');//成功时间
            $table->timestamp('succeeded_at', 0)->nullable()->comment('提现成功时间');//成功时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_withdrawals');
    }
}
