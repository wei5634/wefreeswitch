<?php
//当前通话
namespace app\freeswitch\controller;
use app\freeswitch\controller\Fscommon;
use think\Db;

class Cdr extends Fscommon{
	public function _initialize() {
       	//$this->fp=$this->_esl();
		$sip_hangup_disposition=array(
			'recv_bye'=>'主叫挂断',
			'recv_cancel'=>'主叫取消',
			'send_bye'=>'被叫挂断',
			'send_refuse'=>'被叫拒绝',
		);
		$this->assign('sip_hangup_disposition',$sip_hangup_disposition);
		$this->cdr_table=session('freeswitch_cdr_table');	
	}
    public function index(){
			$sort=input('get.sort');
			$order=input('get.order');		
		
			$db=Db::name($this->cdr_table,"weoa_","DB_CONFIG_AICOMFREESWITCH");
			
			$map['start_epoch']=array('gt',0);
			
			$keywords=input('request.');
			if($keywords['caller']){
				if(strpos($keywords['caller'],'*')===FALSE){
					$map['caller_id_number']=array('eq',$keywords['caller']);				
				}
				else{
					$keywords['caller']=str_replace('*', "%", $keywords['caller']);
					$map['caller_id_number']=array('like',$keywords['caller']);
				}
			}
			if($keywords['called']){
				if(strpos($keywords['called'],'*')===FALSE){
					$map['destination_number']=array('eq',$keywords['called']);
				}
				else{
					$keywords['called']=str_replace('*', "%", $keywords['called']);
					$map['destination_number']=array('like',$keywords['called']);
				}
			}
			
			if(empty($sort)){
				//起始时间
				$order='start_epoch';
				$sort=' desc ';
			}
			$count = $db->where($map)->count('uuid');
			
			//echo $db->getLastSql();
			 if ($count > 0) {
            	import("Lib.Wewe.Wepage");
				$p = new \Wepage($count);
			
				$list=$db->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
				//dump($list);
				//echo $db->getLastSql();
				//分页跳转的时候保证查询条件
				foreach ($map as $key => $val) {
					if (!is_array($val)) {
						$p->parameter .= "$key=" . urlencode($val) . "&";
					}
				}
				$page = $p->show();
			 }
			$this->assign('sort', $sort);
            $this->assign('order', $order);
			$this->assign('list',$list);
			$this->assign("page", $page);
			$this->display();
    }
	public function view(){
		$uuid=input('get.uuid');
		$db=M($this->cdr_table,"weoa_","DB_CONFIG_AICOMFREESWITCH");
		$fs_xml_cdr=$db->where("uuid='$uuid'")->find();
		//dump($fs_xml_cdr);
		if(!empty($fs_xml_cdr['xml_cdr'])){
			$list=simplexml_load_string($fs_xml_cdr['xml_cdr']);
			$list=objectToArray($list);
		}
		//dump($list);
		$this->assign('fs_xml_cdr',$fs_xml_cdr);
		$this->assign('list',$list);
		$this->display();
	}
	
}