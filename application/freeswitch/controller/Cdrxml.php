<?php
/**
 * cdr导入数据库/文件
 * 地址http://www.xx.com/index.php/freeswitch/cdrxml/index/pass/123456/cdr_table/fs_xml_cdr
 */
namespace app\freeswitch\controller;

class Cdrxml extends \think\Controller
{
    public function index(){
        //密码
        $pass=input('pass');
        //cdr表
        $cdr_table=input('cdr_table')?input('cdr_table'):'fs_xml_cdr';
        $xml_cdr_password=config('freeswitch.xml_cdr_password');

        if($pass==$xml_cdr_password){
            $fscdr=new \app\freeswitch\api\FreeswitchCdr();
            //数据库保存
            $xml_cdr_type=config('freeswitch.xml_cdr_type');
            if($xml_cdr_type=='db'){
                $fscdr->xml_save_to_db($_POST,$cdr_table);
            }
            //文件保存 app/freeswitch/xmlcdr/
            elseif(config('freeswitch.xml_cdr_type')=='file'){
                $fscdr->xml_save_to_file($_POST);
            }
        }
        else{
            return json('password error!','404');
        }
	}
	
}
