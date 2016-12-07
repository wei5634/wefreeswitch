<?php
/*
* freeswitch ESL类
* 适用freeswitch ESL
* $esl[esl_host],$esl['esl_port'],$esl['esl_password']
*/

/*
$esl['esl_host']="192.168.0.49";
$esl['esl_port']="8021";
$esl['esl_password']="ClueCon";
$esl=new FreeswitchESL($esl);
$data=$esl->request("status");
var_dump($data;
*/
namespace app\freeswitch\api;

class FreeswitchESL{
    var $host="127.0.0.1";		//地址
    var $port= "8021";    		//端口
    var $password="ClueCon";	//密码
	var $error; //错误信息

    var $fp = null ;
	
	function __construct($esl=array())
    {
        if(!empty($esl['esl_host']) && isset($esl['esl_host']))
            $this->host=$esl['esl_host'];
        if(!empty($esl['esl_port']) && isset($esl['esl_port']))
            $this->port		=$esl['esl_port'];
        if(!empty($esl['esl_port']) && isset($esl['esl_port']))
            $this->password		=$esl['esl_password'];
    }
    /**
     * 当前通话
     *
     * @access      public
     * @return      array
     */
    public function channels(){
        $list=$this->show('channels');

        //处理数据
        foreach((array)$list as $key=>$val){
            //通话时长
            $list[$key]['wei_calltime']=gmstrftime('%H:%M:%S',time()-$val['created_epoch']);

            //取得sip profile
            $name_array = explode("/", $val['name']);
            $sip_profile = $name_array[1];
            $sip_uri = $name_array[2];
            $list[$key]['wei_sip_profile']=$sip_profile;

            //取得主叫号码
            $temp_array = explode("@", $sip_uri);
            $tmp_number = $temp_array[0];
            $tmp_number = str_replace("sip:", "", $tmp_number);
            $list[$key]['wei_number']=$tmp_number;

            //去掉主叫的+号
            //$cid_num = str_replace("+", "", $cid_num);
        }
       return $list;
    }
    /**
     * 在线话机
     *
     * @access      public
     * @param       string     $command  命令 group domain user context
     * @return      array
     */
    public function users($command=''){
        $cmd = "api list_users ".$command;
        //echo $command;
        //echo $cmd;exit;
        $data=$this->request($cmd);
        //分割数据及统计
        $data=explode("\n\n",$data);
        //csv转array
        $list=$this->csv_to_named_array($data[0],'|');
        return $list;
    }
    /**
     * 在线话机类型唯一值
     *
     * @access      public
     * @param       string  $type  类型context domain group user
     * @return      array
     */
    public function users_type($type='context'){
        $allow_type=['context','domain','group','user'];
        if(!in_array(strtolower($type),$allow_type)){
            $this->error='type:'.$type.' not allow,allow type list:'.implode('/',$allow_type);
            return false;
        }

       $list= $this->users();
        foreach((array)$list as $key=>$val){
            $data[]=$val[$type];
        }
        $data=array_unique($data);
        return $data;
    }
    /**
     * 在线话机
     *
     * @access      public
     * @param       string  $profile  profile
     * @return      array
     */
    public function registers($profile='internal'){
        $cmd = "api sofia xmlstatus profile ".$profile." reg ";
        $xml_response=$this->request($cmd);
        try {
            $xml = new \SimpleXMLElement($xml_response);
        }
        catch(Exception $e) {
            $this->error=$e->getMessage();
            return $e->getMessage();
        }
        $list=$this->object_to_array($xml);
        if(!empty($list['registrations'])) {
            if (empty($list['registrations']['registration']['call-id'])) {
                return $list['registrations']['registration'];
            } else {
                $data['0'] = $list['registrations']['registration'];
                return $data;
            }
        }
    }
    /**
     * sofia状态
     *
     * @access      public
     * @return      array
     */
    public function sofia_status(){
        $cmd = "api sofia xmlstatus";

        $xml_response=$this->request($cmd);

        try {
            $xml = new \SimpleXMLElement($xml_response);
        }
        catch(Exception $e) {
            $this->error=$e->getMessage();
            return $e->getMessage();
        }
        $list=$this->object_to_array($xml);

        //处理只有一个数据的，合成二维数组
        foreach((array)$list as $key=>$val) {
            if (array_key_exists('name', $val)) {
                $one_array = $list[$key];
                unset($list[$key]);
                foreach ($one_array as $k => $v) {
                    $list[$key][0][$k] = $v;
                }
            }
        }
        return $list;
    }
    /**
     * sofia状态
     *
     * @access      public
     * @param       string  $profile  profile
     * @return      array
     */
    public function sofia_profile_status($profile=''){
        $cmd = "api sofia xmlstatus";
        if($profile){
            $cmd.=" profile ".$profile;
        }
        $xml_response=$this->request($cmd);

        try {
            $xml = new \SimpleXMLElement($xml_response);
        }
        catch(Exception $e) {
            $this->error=$e->getMessage();
            return $e->getMessage();
        }
        $list=$this->object_to_array($xml);
        return $list['profile-info'];
    }
    /**
     * sofia 网关列表gateway
     *
     * @access      public
     * @param       string  $profile  profile
     * @param       string  $command  command
     * @return      array
     */
    public function gateways($profile,$command=''){
        $cmd = "api sofia profile ".$profile." gwlist ".$command;

        $data=$this->request($cmd);
        if(substr($data,0,1)=='-'){
            return false;
        }
        else{
            $arr=explode(" ",$data);
            if(!empty($arr)) return $arr;

        }
    }
    /**
     * 踢掉在线话机
     *
     * @access      public
     * @param       string  $call_id  id号码 格式133@192.168.0.22
     * @param       string  $profile
     * @return      bool
     */
    public function flush_inbound_reg($call_id,$profile='internal'){
        $cmd = "api sofia profile ".$profile." flush_inbound_reg ".$call_id;
        $data=$this->request($cmd);
        $this->error = $data;
        if(substr($data,0,3)=="+OK")
        {
            return true;
        }
        else {

            return false;
        }
    }
    /**
     * 挂掉当前通话
     *
     * @access      public
     * @param       string  $uuid  uuid
     * @return      bool
     */
    public function uuid_kill($uuid){
        $cmd = "api uuid_kill ".$uuid;
        $data=$this->request($cmd);
        $this->error = $data;
        if(substr($data,0,3)=="+OK")
        {
            return true;
        }
        else {

            return false;
        }
    }
    /**
     * 挂断所有通话
     *
     * @access      public
     * @param       string  $command  命令 <cause> [<variable> <value>]
     * @return      bool
     */
    public function hupall($command=''){
        $cmd = "api hupall ".$command;
        $data=$this->request($cmd);
        $this->error = $data;
        if(substr($data,0,3)=="+OK")
        {
            return true;
        }
        else {

            return false;
        }
    }
    /**
     * 重新加载xml
     *
     * @access      public
     * @return      bool
     */
    public function reloadxml(){
        $cmd = "api reloadxml ";
        $data=$this->request($cmd);
        $this->error = $data;
        if(substr($data,0,3)=="+OK")
        {
            return true;
        }
        else {

            return false;
        }
    }

    /**
     * 重新加载acl
     *
     * @access      public
     * @return      bool
     */
    public function reloadacl(){
        $cmd = "api reloadacl ";
        $data=$this->request($cmd);
        $this->error = $data;
        if(substr($data,0,3)=="+OK")
        {
            return true;
        }
        else {

            return false;
        }
    }
    /**
     * 系统状态
     *
     * @access      public
     * @return      array
     */
    public function status(){
        $cmd = "api status ";
        $data=$this->request($cmd);

        $all=$data;
        //分割数据及统计
        $data=explode("\n",$data);
        //dump($data);

        /*/在线时长
        $server_online_time_array=explode(',',$data[0]);
        foreach($server_online_time_array as $key=>$val){
            preg_match_all("/\d+/",$val,$arr);
            dump($arr[0][0]);
        }*/
        if(!empty($data[0])){
        $list['server_online_time']=trim(str_replace(['UP','years','days','hours','minutes','milliseconds','microseconds','seconds'],['','年','天','小时','分钟','毫秒','微秒','秒'],$data[0]));
        //freeswitch版本
        $list['version']=trim(str_replace('is ready','',$data[1]));
        //freeswtich启动后，运行线程总计
        preg_match_all("/\d+/",$data[2],$total_run_sessions);
        $list['total_run_sessions']=$total_run_sessions[0][0];
        //当前活动通道
        preg_match_all("/\d+/",$data[3],$active_channels);
        $list['active_channels']=$active_channels[0][0];
        //每秒最大线程
        preg_match_all("/\d+/",$data[4],$sessions_per_second);
        $list['sessions_per_second']=$sessions_per_second[0][0];
        //最大线程
        preg_match_all("/\d+/",$data[5],$max_sessions);
        $list['max_sessions']=$max_sessions[0][0];
        //拒绝呼叫前的最小空闲CPU数
        preg_match_all("/\d+.\d+\/\d+.\d+/",$data[6],$min_idle_cpu);
        $list['min_idle_cpu']=$min_idle_cpu[0][0];
        }

        $list['all']=$all;

        return $list;

    }
    /**
     * 版本
     *
     * @access      public
     * @return      string
     */
    public function version(){
        $cmd = "api version";
        return $this->request($cmd);
    }
    /**
     * 关闭freeswitch
     *
     * @access      public
     * @param       string  $type   类型 [asap|asap restart|cancel|elegant|now|restart|restart asap|restart elegant]
     * @return      string
     */
    public function shutdown($type=''){
        if(!empty($type)) {
            $allow_type = ['asap', 'now', 'elegant', 'cancel', 'asap restart', 'restart', 'restart asap', 'restart elegant'];
            if (!in_array(strtolower($type), $allow_type)) {
                $this->error = 'type:' . $type . ' not allow,allow type list:' . implode('/', $allow_type);
                return false;
            }
        }
        $cmd = "fsctl  shutdown ".$type;
        return $this->request($cmd);
    }
    /**
     * 系统各种状态
     *
     * @access      public
     * @param       string  $type   类型
     * codec
    endpoint
    application
    api
    dialplan
    file
    timer
    calls [count]
    channels [count|like <match string>]
    calls
    detailed_calls
    bridged_calls
    detailed_bridged_calls
    aliases
    complete
    chat
    management
    modules
    nat_map
    say
    interfaces
    interface_types
    tasks
    limits
     * @return      array
     */
    public function show($type){
        $allow_type=['aliases','api','application','bridged_calls','calls','channels','chat','codec','complete','detailed_bridged_calls','detailed_calls','dialplan','endpoint','file','interface_types','interfaces','limits','management','modules','nat_map','registrations','say','status','tasks','timer'];
        if(!in_array(strtolower($type),$allow_type)){
            $this->error='type:'.$type.' not allow,allow type list:'.implode('/',$allow_type);
            return false;
        }
        /*
        //csv方式获取
        $cmd = "api show ".$type." ";
        $data=$this->request($cmd);

        //分割数据及统计
        $data=explode("\n\n",$data);
        dump($data);exit;
        if(!empty($data[0])){
            //csv转array
            $list=$this->csv_to_named_array(trim($data[0]),',');
            //
            return $list;
        }
        */

        //* 以下获取xml方式
        $cmd = "api show ".$type." as xml";
        $xml_response=$this->request($cmd);
        try {
            $xml = new \SimpleXMLElement($xml_response);
        }
        catch(Exception $e) {
            $this->error=$e->getMessage();
            return $e->getMessage();
        }
        //dump($xml);
        $list=$this->object_to_array($xml);
        unset($list["@attributes"]);

        //dump($list);
        if(array_key_exists('row',$list)){
            //处理只有一个数据的，合成二位数组
            if(array_key_exists('type',$list['row'])){
                unset($list['row']["@attributes"]);
                $one_array=$list['row'];
                unset($list);
                foreach((array)$one_array as $key=>$val){
                    $list['row']['0'][$key]=$val;
                }
            }
            return $list['row'];
        }
    }
    /**
     * api
     *
     * @access      public
     * @param       string         $command    api命令
     * @return      mixed
     */
    public function api($command){
        $command="api ".$command;
        $response=$this->request($command);
        return $response;
    }
    /**
     * bgapi
     *
     * @access      public
     * @param       string         $command    api命令
     * @return      mixed
     */
    public function bgapi($command){
        $command="bgapi ".$command;
        $response=$this->request($command);
        return $response;
    }
    /**
     * event事件
     *
     * @access      public
     * @param       string     $output_type 输出类型'json','xml','plain'
     * @param       string     $event_list  Event List
     * @return      string
     */
    public function event($output_type='json',$event_list='ALL'){
        $allow_type=['json','xml','plain'];
        if(!in_array(strtolower($output_type),$allow_type)){
            $this->error='type:'.$output_type.' not allow,allow type list:'.implode('/',$allow_type);
            return false;
        }
        $command="event ".$output_type." ".strtoupper($event_list);
        $response=$this->request($command);
        return $response;
    }
	/**
	* 创建esl socket连接
	*
	* @access      private
	* @return 	   handle
	*/
    public function create()
    {
        //连接服务器
        try {
		    $this->fp = fsockopen($this->host, $this->port, $errno, $errdesc);
		    socket_set_blocking($this->fp, false);//非阻塞模式
        }
        catch (Exception $e){
            $this->error=$e->getMessage();
            echo $e->getMessage();
        }
	
		if (!$this->fp) {
			$this->error="error invalid handle<br />\n";
			$this->error.="error number: ".$errno."<br />\n";
			$this->error.="error description: ".$errdesc."<br />\n";
			return false;
		}
		else {
			while (!feof($this->fp)) {
				$buffer = fgets($this->fp, 1024);
                //等待响应100ms
				usleep(100);
				if (trim($buffer) == "Content-Type: auth/request") {
					 fputs($this->fp, "auth $this->password\n\n");
					 break;
				}
			}
			return $this->fp;
		}
	}

	/**
	* esl命令请求
	*
	* @access      private
	* @param       string         $cmd    命令
	* @return      string
	*/
    public function request($cmd)
    {
        if( is_null( $this->fp )  ){
            $this->create();
        }
        //处理url格式
        $cmd=trim(urldecode($cmd));
        if ($this->fp) {
            fputs($this->fp, $cmd . "\n\n");
            //等待响应100ms
            usleep(100);

            $response = "";
            $i = 0;
            $contentlength = 0;
            while (!feof($this->fp)) {
                $buffer = fgets($this->fp, 4096);
                if ($contentlength > 0) {
                    $response .= $buffer;
                }

                if ($contentlength == 0) { //如果内容没有长度，则不要再处理
                    if (strlen(trim($buffer)) > 0) { //只有当缓冲区有内容时才运行
                        $temparray = explode(":", trim($buffer));
                        if ($temparray[0] == "Content-Length") {
                            $contentlength = trim($temparray[1]);
                        }
                    }
                }
                //等待响应20ms
                usleep(20);

                //防止一个无休止的循环 //可选的，脚本超时
                if ($i > 10000) {
                    break;
                }

                if ($contentlength > 0) { //contentlength 存在
                    //如果所有的内容已被读取,停止读取.
                    if (strlen($response) >= $contentlength) {
                        break;
                    }
                }
                $i++;
            }

            return trim($response);
        } else {
            $this->error = "request no handle";
            return false;
        }
    }
    /**
     * fclose关闭$fp handle
     *
     * @access      private
     * @return
     */
    public function close()
    {
        if( !is_null( $this->fp )  ) {
            fclose($this->fp);
        }
    }
	/**
	* CSV格式转数组
	*
	* @access      private
	
	* @param       string         $tmp_str    字符串
	* @param       string         $tmp_delimiter    分隔符
	* @return      array
	*/
    public function csv_to_named_array($tmp_str, $tmp_delimiter) {
        $tmp_array = explode ("\n", $tmp_str);
        $result = '';
        if (trim(strtoupper($tmp_array[0])) != "+OK") {
            //取第一行当作键值
            $tmp_field_name_array = explode ($tmp_delimiter, $tmp_array[0]);
            $x = 0;
            foreach ($tmp_array as $row) {
                if ($x > 0) {
                    $tmp_field_value_array = explode ($tmp_delimiter, $tmp_array[$x]);
                    $y = 0;
                    foreach ($tmp_field_value_array as $tmp_value) {
                        $tmp_name = $tmp_field_name_array[$y];

                        if (trim(strtoupper($tmp_value)) != "+OK") {
                            $result[$x][$tmp_name] = $tmp_value;
                        }
                        $y++;
                    }
                }
                $x++;
            }
            unset($row);
        }
        return $result;
    }
    /**
     * 秒数转化成 小时:分钟:秒
     *
     * @access      public
     * @return      string
     */
    public function seconds_to_hour_min($seconds){
        if ($seconds>3600){
            $hours = intval($seconds/3600);
            $minutes = $seconds/60;
            $time = $hours.":".gmstrftime('%M:%S', $minutes);
        }else{
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }
    // ------------------------------------------------------------------------
    /**
     * 多维对像转多维数组
     *
     * @access  private
     * @return string
     */
    public function object_to_array($object) {
        $arr=json_decode(json_encode( $object),true);
        $arr2=$this->del_empty_array($arr);
        return  $arr2;
    }
    /**
     * array()变为空
     *
     * @access  private
     * @return string
     */
    public function del_empty_array($arr) {
        if(empty($arr)) $arr="";
        if(is_array($arr)){
           return array_map(__METHOD__, $arr);
        }else{
            return $arr;
        }
    }

	/**
	* 取得最后的错误信息
	*
	* @access      public
	* @return      string
	*/
	public function get_error() {
		return $this->error;
	}
}
?>