<?php
/**
 * 命令行
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;
use think\Request;

class Command extends Fscommon{
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/command.php');
    }
    //首页
    function index(Request $request){
        $command=$list=null;
       if($request->isPost()){
            $command=input('command');
            //dump($command);exit;
            $freeswitch_esl_key=session('freeswitch_esl');
            $esl=config('freeswitch_esl')[$freeswitch_esl_key];
            $fs=new \app\freeswitch\api\FreeswitchESL($esl);
            $data=$fs->api($command);
            $fs->close();
            $list=htmlentities($data);
        }
        $this->assign('command',$command);
        $this->assign('list',$list);
        return $this->fetch();
    }
}