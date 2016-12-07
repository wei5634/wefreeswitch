<?php
/**
 * sofia sip
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;

class Sofia extends Fscommon{
    var $esl=null;
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/sofia.php');

        $freeswitch_esl_key=session('freeswitch_esl');
        $this->esl=config('freeswitch_esl')[$freeswitch_esl_key];
    }
    //首页
    function index(){
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        //系统状态
        $status=$fs->status();
        //sofia状态
        $list=$fs->sofia_status();

        //网关profile
        if($list['profile']){
            foreach($list['profile'] as $key=>$val){
                $gwlist=$fs->gateways($val['name']);
                foreach((array)$gwlist as $k=>$v){
                    if(!empty($v))
                    $gateway_profile[$v]=$val['name'];
                }
            }
        }
        $this->assign('gateway_profile',$gateway_profile);
        $this->assign('status',$status['all']);
        $this->assign('list',$list);
        //halt($list);
        return $this->fetch();
    }
    //reloadxml
    function reloadxml(){
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->reloadxml();
        $this->success($fs->get_error());
    }
    //reloadacl
    function reloadacl(){
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->reloadacl();
        $this->success($fs->get_error());
    }
    //profile状态
    function profileStatus($profile='internal'){
        $name=input('name');
        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);
        $list=$fs->sofia_profile_status($profile);

        $this->assign('name',$name);
        $this->assign('list',$list);
        //halt($list);
        return $this->fetch();
    }
}