<?php
/**
 * freeswitch通用控制器
 */

namespace app\freeswitch\controller;


class Fscommon extends \think\Controller
{
    var $esl=null;
    //初始化
    public function _initialize()
    {
        $freeswitch_esl=session('freeswitch_esl');
        if(!isset($freeswitch_esl)) {
            //存在多个服务器
            if (count(config('freeswitch_esl')) > 1) {
                $this->redirect('publics/select');
            } else {//只有一个服务器
                //取第一个esl信息，保存
                session('freeswitch_esl','0');
                $this->redirect('sofia/index');
            }
        }
        $freeswitch_esl_key=session('freeswitch_esl');
        //找不到key，重新选择
        if(empty(config('freeswitch_esl')[$freeswitch_esl_key])){
            $this->redirect('publics/select');
        }
        $freeswitch_esl_name=config('freeswitch_esl')[$freeswitch_esl_key]['server_name']?config('freeswitch_esl')[$freeswitch_esl_key]['server_name']:config('freeswitch_esl')[$freeswitch_esl_key]['host'];
        session('freeswitch_esl_name',$freeswitch_esl_name);
        $this->esl=config('freeswitch_esl')[$freeswitch_esl_key];
    }
    //命令行运行 只支持api
    public function cmd($command)
    {
        if(empty($command)) $this->error('请输入api命令');

        $fs=new \app\freeswitch\api\FreeswitchESL($this->esl);

        $list=$fs->api($command);
        $this->success($list);
    }
}