<?php
/**
 * 系统各项状态
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;

class Show extends Fscommon{
    var $esl=null;
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/show.php');

        $freeswitch_esl_key=session('freeswitch_esl');
        $this->esl=config('freeswitch_esl')[$freeswitch_esl_key];
    }
    //首页
    function index($type='status'){
        if($type=='status') $this->redirect('status');
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->show($type);

        $this->assign('list',$list);
        //dump($list);
        return $this->fetch($type);
    }
    //系统状态
    function status(){
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->status();

        $this->assign('list',$list);
        //halt($list);
        return $this->fetch();
    }
}