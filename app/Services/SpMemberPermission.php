<?php

namespace App\Services;

use App\Exceptions\AppException;
use Zdp\ServiceProvider\Data\Models\SpMemberPermission as PermissionModel;

class SpMemberPermission
{
    /**
     * 获取权限列表
     *
     * @param string $memberOpenId
     * @param int    $type
     */
    public function getMemberPermissions(
        $memberOpenId,
        $type = PermissionModel::TYPE_WECHAT
    ) {
        if (!isSpOwner()) {
            throw new AppException('没有权限');
        }

        $permissions = PermissionModel
            ::query()
            ->where('wechat_openid', $memberOpenId)
            ->where('type', $type)
            ->pluck('permission');

        return $permissions->toArray();
    }

    /**
     * @param int $type
     *
     * @return array
     * @throws AppException
     */
    public function getCurrentPermissions($type = PermissionModel::TYPE_WECHAT)
    {
        if (isSpOwner()) {
            return ['all'];
        }

        $memberWecahtInfo = getWeChatOAuthUser();

        if (empty($memberWecahtInfo)) {
            throw new AppException('找不到微信用户信息');
        }

        $openId = $memberWecahtInfo->getId();

        $permissions = PermissionModel
            ::query()
            ->where('wechat_openid', $openId)
            ->where('type', $type)
            ->pluck('permission');

        return $permissions->toArray();
    }

    /**
     * 设置权限
     *
     * @param string  $memberOpenId
     * @param string  $permission
     * @param boolean $enabled
     * @param int     $type
     */
    public function setPermissions(
        $memberOpenId,
        $permission,
        $enabled,
        $type = PermissionModel::TYPE_WECHAT
    ) {
        if (!isSpOwner()) {
            throw new AppException('没有权限');
        }

        if ($enabled) {
            PermissionModel
                ::query()
                ->firstOrCreate([
                    'wechat_openid' => $memberOpenId,
                    'permission'    => $permission,
                    'type'          => $type,
                ]);
        } else {
            PermissionModel
                ::query()
                ->where('wechat_openid', $memberOpenId)
                ->where('permission', $permission)
                ->where('type', $type)
                ->delete();
        }
    }
}