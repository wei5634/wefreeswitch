<?php
/**
 * 首页
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;

class Index extends Fscommon{
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/index.php');
    }
    //首页
    function index(){
        $freeswitch_esl=session('freeswitch_esl');
        if(isset($freeswitch_esl)) {
            //存在多个服务器
            if (count(config('freeswitch_esl')) > 1) {
                $this->redirect('publics/select');
            } else {//只有一个服务器
                $this->redirect('sofia/index');
            }
        }
    }
    //多服务器选择
    public function select(){
        $list=config('freeswitch_esl');
        $this->assign('list',$list);
        return $this->fetch();
    }
    //多服务器选择操作
    public function selectdo(){
        session('freeswitch_esl',input('key'));
        $this->redirect('sofia/index');
    }
}