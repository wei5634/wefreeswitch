<?php
/**
 * 在线话机
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;

class Users extends Fscommon{
    var $esl=null;
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/users.php');

        $freeswitch_esl_key=session('freeswitch_esl');
        $this->esl=config('freeswitch_esl')[$freeswitch_esl_key];
    }
    //首页
    function index(){
        $command=input('command');
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);

        //context列表
        $context_list=$fs->users_type('context');
        $this->assign('context_list',$context_list);

        //group列表
        $group_list=$fs->users_type('group');
        $this->assign('group_list',$group_list);

        //context列表
        $domain_list=$fs->users_type('domain');
        $this->assign('domain_list',$domain_list);

        //话机列表
        $list=$fs->users($command);
        $this->assign('list',$list);
        //halt($list);
        return $this->fetch();
    }
}