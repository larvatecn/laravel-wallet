<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id')->comment('交易 id');
            $table->unsignedBigInteger('user_id')->index()->comment('用户 id');
            $table->integer('amount')->comment('交易金额');//金额 单位分
            $table->unsignedInteger('available_amount')->comment('交易后余额');//该笔交易发生后，用户的余额,单位分
            $table->string('description')->comment('交易描述');//描述
            $table->morphs('source');//关联对象
            $table->string('type')->comment('交易类型');
            $table->ipAddress('client_ip')->nullable()->comment('用户IP');//发起支付请求客户端的 IP 地址
            $table->timestamp('created_at', 0)->nullable()->comment('创建时间');//创建时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
