<?php
namespace app\api\controller\v2;
use app\api\model\User as UserMode;

class User {
    public function read($id=0){
        $user=UserMode::get($id,'profile');
        if($user){
            return json($user);
        }
        else{
            return json('用户不存在');
        }
    }
}