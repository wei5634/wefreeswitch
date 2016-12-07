<?php
/**
 * 在线话机
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;

class Registers extends Fscommon{
    var $esl=null;
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/registers.php');

        $freeswitch_esl_key=session('freeswitch_esl');
        $this->esl=config('freeswitch_esl')[$freeswitch_esl_key];
    }
    //首页
    function index($profile='internal'){
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->registers($profile);

        $this->assign('profile',$profile);
        $this->assign('list',$list);
        //halt($list);
        return $this->fetch();
    }
    //挂断通话
    function flushReg(){
        $data=[
            'call_id'=>input('call_id'),
            'profile'=>input('profile')
        ];
        $this->error($this->validate($data,'Registers'));

        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $res=$fs->flush_inbound_reg($data['call_id'],$data['profile']);
        if($res) $this->redirect('index',['profile'=>$data['profile']]);
    }
}