<?php
/**
 * freeswitch CDR历史通话
 */
namespace app\freeswitch\api;
use \think\Db;

class FreeswitchCdr{
    function __construct()
    {

    }
    /**
     * xml格式保存文件
     *
     * @access      public
     * @param   string  $post_data
     * @param   string  $filepath     文件目录
     * @return      array
     */
    function xml_save_to_file($post_data,$filepath='') {
        $filepath=empty($filepath)?APP_PATH.'freeswitch/xmlcdr/':$filepath;

        set_time_limit(3600);
        ini_set('memory_limit', '256M');
        Header("Content-type: text/plain");
        config('show_error_msg',false);
        config('app_trace',false);
        //dump($post_data);exit;

        if (strlen($post_data["cdr"]) > 0) {
            $filename=!empty($post_data['uuid'])?$post_data['uuid']:uniqid();
            $fh = fopen($filepath.$filename.".cdr.xml.", 'wb');
            fwrite($fh, $post_data['cdr']);
            fclose($fh);
        }
    }
    /**
     * xml格式保存到数据库
     *
     * @access      public
     * @param   string  $post_data
     * @param   string  $filepath     文件目录
     * @return      array
     */
    function xml_save_to_db($post_data,$cdr_table='fs_xml_cdr') {
        set_time_limit(3600);
        ini_set('memory_limit', '256M');
        Header("Content-type: text/plain");
        config('show_error_msg',false);
        config('app_trace',false);

        if (strlen($post_data["cdr"]) > 0) {
            if (substr($post_data['uuid'], 0, 2) == "a_") {
                $leg = "a";
            } else {
                $leg = "b";
            }
            $this->process_xml_cdr($leg,$post_data["cdr"],$cdr_table);
        }
    }
    /**
     * 处理xml格式CDR
     *
     * @access      public
     * @param   string  $leg    a/b腿
     * @param   string  $xml_string     xml格式字符
     * @return      array
     */
    function process_xml_cdr($leg, $xml_string,$cdr_table) {
        try {
            $xml = simplexml_load_string($xml_string);
        }
        catch(Exception $e) {
            echo $e->getMessage();
        }

        //服务器名
        //$xml_array=objectToArray($xml);
        //$data['switchname']= $this->check_str(urldecode($xml_array['variables']['FreeSWITCH-Switchname']));

        //misc
        $uuid = $this->check_str(urldecode($xml->variables->uuid));
        $data['uuid'] = $uuid;
        $data['accountcode'] = $this->check_str(urldecode($xml->variables->accountcode));
        $data['default_language'] = $this->check_str(urldecode($xml->variables->default_language));
        $data['bridge_uuid'] = $this->check_str(urldecode($xml->variables->bridge_uuid));
        $data['digits_dialed'] = $this->check_str(urldecode($xml->variables->digits_dialed));
        $data['sip_hangup_disposition'] = $this->check_str(urldecode($xml->variables->sip_hangup_disposition));
        //时间 time
        $data['start_epoch'] = $this->check_str(urldecode($xml->variables->start_epoch));
        $start_stamp = $this->check_str(urldecode($xml->variables->start_stamp));
        $data['start_stamp'] = $start_stamp;
        $data['answer_stamp'] = $this->check_str(urldecode($xml->variables->answer_stamp));
        $data['answer_epoch'] = $this->check_str(urldecode($xml->variables->answer_epoch));
        $data['end_epoch'] = $this->check_str(urldecode($xml->variables->end_epoch));
        $data['end_stamp'] = $this->check_str(urldecode($xml->variables->end_stamp));
        $data['duration'] = $this->check_str(urldecode($xml->variables->duration));
        $data['mduration'] = $this->check_str(urldecode($xml->variables->mduration));
        $data['billsec'] = $this->check_str(urldecode($xml->variables->billsec));
        $data['billmsec'] = $this->check_str(urldecode($xml->variables->billmsec));
        //编码 codecs
        $data['read_codec'] = $this->check_str(urldecode($xml->variables->read_codec));
        $data['read_rate'] = $this->check_str(urldecode($xml->variables->read_rate));
        $data['write_codec'] = $this->check_str(urldecode($xml->variables->write_codec));
        $data['write_rate'] = $this->check_str(urldecode($xml->variables->write_rate));
        $data['remote_media_ip'] = $this->check_str(urldecode($xml->variables->remote_media_ip));
        $data['hangup_cause'] = $this->check_str(urldecode($xml->variables->hangup_cause));
        $data['hangup_cause_q850'] = $this->check_str(urldecode($xml->variables->hangup_cause_q850));
        //呼叫中心 call center
        $data['cc_side'] = $this->check_str(urldecode($xml->variables->cc_side));
        $data['cc_member_uuid'] = $this->check_str(urldecode($xml->variables->cc_member_uuid));
        $data['cc_queue_joined_epoch'] = $this->check_str(urldecode($xml->variables->cc_queue_joined_epoch));
        $data['cc_queue'] = $this->check_str(urldecode($xml->variables->cc_queue));
        $data['cc_member_session_uuid'] = $this->check_str(urldecode($xml->variables->cc_member_session_uuid));
        $data['cc_agent'] = $this->check_str(urldecode($xml->variables->cc_agent));
        $data['cc_agent_type'] = $this->check_str(urldecode($xml->variables->cc_agent_type));
        $data['waitsec'] = $this->check_str(urldecode($xml->variables->waitsec));
        //app info
        $data['last_app'] = $this->check_str(urldecode($xml->variables->last_app));
        $data['last_arg'] = $this->check_str(urldecode($xml->variables->last_arg));
        //会议 conference
        $data['conference_name'] = $this->check_str(urldecode($xml->variables->conference_name));
        $data['conference_uuid'] = $this->check_str(urldecode($xml->variables->conference_uuid));
        $data['conference_member_id'] = $this->check_str(urldecode($xml->variables->conference_member_id));

        //get the values from the callflow.
        $x = 0;
        foreach ($xml->callflow as $row) {
            if ($x == 0) {
                $context = $this->check_str(urldecode($row->caller_profile->context));
                $data['destination_number'] = $this->check_str(urldecode($row->caller_profile->destination_number));
                $data['context'] = $context;
                $data['network_addr'] = $this->check_str(urldecode($row->caller_profile->network_addr));
            }
            $data['caller_id_name'] = $this->check_str(urldecode($row->caller_profile->caller_id_name));
            $data['caller_id_number'] = $this->check_str(urldecode($row->caller_profile->caller_id_number));
            $x++;
        }
        unset($x);

        //哪条腿 store the call leg
        $data['leg'] = $leg;

        //store the call direction
        $data['direction'] = $this->check_str(urldecode($xml->variables->direction));

        //store post dial delay, in milliseconds
        $data['pdd_ms'] = $this->check_str(urldecode($xml->variables->progress_mediamsec) + urldecode($xml->variables->progressmsec));

        //get break down the date to year, month and day
        $tmp_time = strtotime($start_stamp);
        $tmp_year = date("Y", $tmp_time);
        $tmp_month = date("M", $tmp_time);
        $tmp_day = date("d", $tmp_time);

        //get the domain values from the xml
        $domain_name = $this->check_str(urldecode($xml->variables->domain_name));
        $domain_uuid = $this->check_str(urldecode($xml->variables->domain_uuid));

        //get the domain_uuid with the domain_name
        /*
            if (strlen($domain_uuid) == 0) {
                $sql = "select domain_uuid from v_domains ";
                if (strlen($domain_name) == 0 && $context != 'public' && $context != 'default') {
                    $sql .= "where domain_name = '".$context."' ";
                }
                else {
                    $sql .= "where domain_name = '".$domain_name."' ";
                }
                $row = $db->query($sql)->fetch();
                $domain_uuid = $row['domain_uuid'];
                if (strlen($domain_uuid) == 0) {
                    $sql = "select domain_name, domain_uuid from v_domains ";
                    $row = $db->query($sql)->fetch();
                    $domain_uuid = $row['domain_uuid'];
                    if (strlen($domain_name) == 0) { $domain_name = $row['domain_name']; }
                }
            }
        */
        //set values in the database
        $data['domain_uuid'] = $domain_uuid;
        $data['domain_name'] = $domain_name;

        //check whether a recording exists
        $recording_relative_path = '/archive/'.$tmp_year.'/'.$tmp_month.'/'.$tmp_day;
        //if (file_exists($_SESSION['switch']['recordings']['dir'].$recording_relative_path.'/'.$uuid.'.wav')) {
            $recording_file = $recording_relative_path.'/'.$uuid.'.wav';
        //}
        //elseif (file_exists($_SESSION['switch']['recordings']['dir'].$recording_relative_path.'/'.$uuid.'.mp3')) {
            $recording_file = $recording_relative_path.'/'.$uuid.'.mp3';
        //}
        if(isset($recording_file) && !empty($recording_file)) {
            $data['recording_file'] = $recording_file;
        }

        //determine where the xml cdr will be archived
        /*
            $sql = "select * from v_vars ";
            $sql .= "where var_name = 'xml_cdr_archive' ";
            $row = $db->query($sql)->fetch();
            $var_value = trim($row["var_value"]);
            switch ($var_value) {
            case "dir":
                $xml_cdr_archive = 'dir';
                break;
            case "db":
                $xml_cdr_archive = 'db';
                break;
            case "none":
                $xml_cdr_archive = 'none';
                break;
            default:
                $xml_cdr_archive = 'dir';
                break;
            }
        */
        //if xml_cdr_archive is set to db then insert it.
        //if ($xml_cdr_archive == "db") {
        //$data['xml_cdr'] = $xml_string;
        //}

        //insert the $this->check_str($extension_uuid)
        if (strlen($xml->variables->extension_uuid) > 0) {
            $data['extension_uuid'] = $this->check_str(urldecode($xml->variables->extension_uuid));
        }
        //dump($data);exit;
        Db::name($cdr_table)->insert($data);
        return $data;
    }
    /**
     * 处理格式
     *
     * @access      public
     * @param   string  $string
     * @return      array
     */
    function check_str($string) {

        //$tmp_str = mysqli_real_escape_string($string);
        $tmp_str = $string;
        if (strlen($tmp_str)) {
            $string = $tmp_str;
        }
        else {
            $search = array("\x00", "\n", "\r", "\\", "'", "\"", "\x1a");
            $replace = array("\\x00", "\\n", "\\r", "\\\\" ,"\'", "\\\"", "\\\x1a");
            $string = str_replace($search, $replace, $string);
        }
        return trim($string);
    }
}