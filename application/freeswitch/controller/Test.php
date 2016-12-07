<?php
/**
 * 测试用
 **/
namespace app\freeswitch\controller;

class Test extends \think\Controller{
    public function index(){
        dump(config());
    }

    public function testxmlcdr(){
        $str=file_get_contents(APP_PATH.'freeswitch/api/a_a3c04e11-b2eb-4bf5-b868-31cbf1767f47.cdr.xml');
        $postdata=['cdr'=>$str,'uuid'=>'a_a3c04e11-b2eb-4bf5-b868-31cbf1767f47'];
        echo $this->curl_post('http://localhost/wefreeswitch/freeswitch/cdrxml/index/pass/123456',$postdata);
    }

    public function curl_post($url,$post_data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}