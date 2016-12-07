<?php
/**
 * 配置
 **/
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;
use think\Request;

class Config extends Fscommon{
    //初始化
    public function _initialize()
    {
        parent::_initialize();
        //加载首页语言
        \think\Lang::load(APP_PATH.'freeswitch/lang/config.php');
    }
    //首页
    function index(Request $request,$type='system'){
        $list=null;
        $list=config($type);
        //post提交
        if($request->isPost()){
            $data=$request->post('data/a');
            switch ($type){
                case 'freeswitch_esl':
                    $num=count($data['act']);
                    if($num>0){
                        $j=1;
                        //取得排序最大值
                        $sid_array=rsort($data['sid']);
                        $max_sid=$sid_array[0];

                        for($i=0;$i<$num;$i++){
                            if($data['act'][$i]=='update'){
                                $data_esl[$i]['server_name']=$data['server_name'][$i];
                                $data_esl[$i]['esl_host']=$data['esl_host'][$i];
                                $data_esl[$i]['esl_port']=$data['esl_port'][$i];
                                $data_esl[$i]['esl_password']=$data['esl_password'][$i];
                                $data_esl[$i]['sid']=$data['sid'][$i];
                            }
                            elseif ($data['act'][$i]=='insert'){
                                if($data['server_name'][$i]){
                                    $data_esl[$i]['server_name']=$data['server_name'][$i];
                                    $data_esl[$i]['esl_host']=$data['esl_host'][$i];
                                    $data_esl[$i]['esl_port']=$data['esl_port'][$i];
                                    $data_esl[$i]['esl_password']=$data['esl_password'][$i];
                                    if(empty($data['sid'][$i])){
                                        $data_esl[$i]['sid']=$max_sid+$j;
                                        $j++;
                                    }
                                    else{
                                        $data_esl[$i]['sid']=$data['sid'][$i];
                                    }
                                }
                            }
                        }//end for
                        //sid排序
                        $data_esl=array_multi_sort_by_field($data_esl,'sid','SORT_ASC');
                    }//end if
                    write_cache($type,$data_esl,'application'.DS.'extra'.DS);
                    break;
                default:
                    write_cache($type,$data,'application'.DS.'extra'.DS);
                    break;
            }
            $this->success('修改成功!');

        }
        //模板文件
        switch ($type){
            case 'freeswitch_esl':
                $tpl=$type;
                foreach((array)$list as $key=>$val){
                    $list[$key]['sid']=$key;
                }
                break;
            default:
                $tpl='index';
                break;
        }

        $this->assign('type',$type);
        $this->assign('list',$list);
        return $this->fetch($tpl);
    }
}