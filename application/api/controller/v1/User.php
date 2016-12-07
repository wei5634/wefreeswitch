<?php
namespace app\api\controller\v1;
use app\api\model\User as UserMode;

class User {
    public function read($id=0){
        try {
// 制造一个方法不存在的异常
            $user = UserModel::get($id, 'profile');
            if ($user) {
                return json($user);
            } else {
                return abort(404, '用户不存在');
            }
        } catch (\Exception $e) {
// 捕获异常并转发为HTTP异常
            return abort(404, $e->getMessage());
        }
    }
}