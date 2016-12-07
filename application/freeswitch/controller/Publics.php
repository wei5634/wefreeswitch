<?php
/**
 * Public
 **/
namespace app\freeswitch\controller;

class Publics extends \think\Controller{
    //初始化
    public function _initialize()
    {
        //parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/public.php');
    }
    //多服务器选择
    public function select(){
        $list=config('freeswitch_esl');
        $this->assign('list',$list);
        return $this->fetch();
    }
    //多服务器选择操作
    public function selected(){
        session('freeswitch_esl',input('key'));
        $this->redirect('sofia/index');
    }
}