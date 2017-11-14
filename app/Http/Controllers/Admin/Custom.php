<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebAdminUser;
use EasyWeChat\Core\Exceptions\HttpException;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\User as UserModel;

class Custom extends Controller
{

    public function index(Request $request, WebAdminUser $user)
    {
        $sp_id = $user->getSp()->zdp_user_id;
        $query = UserModel::query()
                          ->where('sp_id', $sp_id)
                          ->orderBy('id', 'desc')
                          ->with(['addresses', 'shopType']);

        $search = $request->input('search');

        if (is_numeric($search)) {
            $query->where('mobile_phone', 'like', "%{$search}%");
        } elseif (!empty($search)) {
            $query->where('shop_name', 'like', "%{$search}%");
        }

        $page = $query->paginate(
            $request->input('size', 20),
            ['*'],
            null,
            $request->input('page', 1)
        );

        $list = $page->items();

        $result = [
            'last_page' => $page->lastPage(),
            'current'   => $page->currentPage(),
        ];

        $customs = [];

        foreach ($list as $user) {
            $customs[] = $user->format();
        }

        $result['customs'] = $customs;

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $result,
        ]);
    }

    /**
     * 更新用户
     *
     * @param UserModel    $user
     * @param Request      $request
     * @param WebAdminUser $admin
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws HttpException
     */
    public function update(
        UserModel $user,
        Request $request,
        WebAdminUser $admin
    ) {
        $sp_id = $admin->getSp()->zdp_user_id;
        if ($user->sp_id != $sp_id) {
            throw new HttpException(403, '非子用户');
        }

        $this->validate($request, [
            'name' => 'string|between:1,10',
        ]);

        if (!empty($request->input('name'))) {
            $user->shop_name = $request->input('name');
        }
        $user->save();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }
}