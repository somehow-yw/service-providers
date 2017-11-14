<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Zdp\ServiceProvider\Data\Models\SpMemberPermission;
use Zdp\ServiceProvider\Data\Models\SpMember;

class CreateSpMemberPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_member_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wechat_openid', 32)->comment('微信openid')->index();
            $table->string('permission', 16)->comment('权限名')->index();
            $table->unsignedTinyInteger('type')->comment('权限类型')->index();
            $table->timestamps();
            $table->unique(['wechat_openid', 'permission']);
        });

        SpMember::query()
                ->chunk(100, function ($members) {
                    foreach ($members as $member) {
                        $this->initializePermission($member);
                    }
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_member_permission');
    }

    /**
     * @param SpMember $member
     */
    protected function initializePermission($member)
    {
        $defaultPermissions = [
            'ticket', // 销售单
            'market', // 市场管理
            'price', // 商品改价
            'blacklist', // 商品屏蔽
            'stick', // 商品置顶
            'mall', // 冻品商城
            'shop-info', // 店铺信息
            'custom', // 客户管理
            'pay', // 收款管理
        ];

        foreach ($defaultPermissions as $permission) {
            SpMemberPermission::query()->firstOrCreate([
                'wechat_openid' => $member->wechat_openid,
                'permission'    => $permission,
                'type'          => SpMemberPermission::TYPE_WECHAT,
            ]);
        }
    }
}
