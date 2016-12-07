<?php 
//缓存函数，用于静态缓存
/**
* 检查生存周期
* @param  file  	$cachefile		缓存文件
* @param  int 		$life			生存周期秒计
* @return time or false
*/
function check_cache($cachefile, $life = -1) {
    $timestamp=time();
    if(file_exists($cachefile)) {
        return $life < 0 || $timestamp - @filemtime($cachefile) < $life;
    } else {
        return false;
    }
}

/**
* 读取缓存
* @param  string    $file    文件名
* @param  string  $lift    生存周期
* @param  string  $cachedir    缓存文件目录
* @return array or string
*/
function read_cache($file,$life=-1,$cachedir = ''){
	$cachedir = $cachedir ? WECMS_ROOT . $cachedir : WECMS_ROOT.'Data/static/';
	
	$cachefile = $cachedir.$file.'.php';	
	
    if(check_cache($cachefile,$life)) {
        return read_cachefile($cachefile);
    }
}

/**
* 读取缓存文件
* @param  file    $cachefile    缓存文件
* @param  string  $mode 		模式 include or get_contents
* @return array or string
*/
function read_cachefile($cachefile, $mode = 'i') {
    if(!file_exists($cachefile)) return false;
    return $mode == 'i' ? include $cachefile : file_get_contents($cachefile);
}

/**
* 写缓存
* @param  name 	  	$cachename		缓存文件名
* @param  array 	$cachedata		缓存数组
 *  @param  dir 		$cachedir		生成文件目录
* @param  php/js 	$extra			js生成js文件　$extra $mod都为空生成php文件
* @param  return 	$mod			空:生成php文件格式,js:生成js文件格式,return:生成php文件格式包含return
* @return write file
*/
function write_cache($cachename, $cachedata = '',$cachedir = '',$extra='',$mod='return') {
    $cachedir = $cachedir ? ROOT_PATH . $cachedir : ROOT_PATH.'data/static/';
	
	if($extra == 'js') {
        $filename = $cachename.'.js';
    }
	else{
    	$filename = $cachename.'.php';
	}
    if($extra != 'js') {
    	$cachedata=arrayeval($cachedata);
	}
    
    if(!is_dir($cachedir)) {
        @mkdir($cachedir, 0777);
    }
    $cachefile = $cachedir.$filename;
    $fp = @fopen($cachefile, 'wb');
    if($fp) {
        if(!$extra && !$mod) {
            @fwrite($fp, "<?php\r\n\r\n".$cachedata."\r\n\r\n?>");
        }
		elseif($extra == 'js') {
            @fwrite($fp, "".$cachedata."\r\n");
        }
        elseif($mod == 'return') {
            @fwrite($fp, "<?php \r\nreturn $cachedata; \r\n?>");
        }
        @fclose($fp);
        @chmod($cachefile, 0777);
    } else {
        echo 'Can not write to '.$filename.' cache files, please check directory '.$cachedir;
        exit;
    }
}

/**
* 字段转生成格式
* @param  name 	  	$array		数组
* @param  array 	$level		级别
* @return array
*/
function arrayeval($array, $level = 0) {

	if(!is_array($array)) {
		return "'".$array."'";
	}
	if(is_array($array) && function_exists('var_export')) {
		return var_export($array, true);
	}
	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "array (\n\r";
	$comma = $space;
	if(is_array($array)) {
		foreach($array as $key => $val) {
			$key = is_string($key) ? '\''.add_cs_lashes($key).'\'' : $key;
			$val = !is_array($val) && (!preg_match("/^\-?[0-9]\d*$/", $val) || strlen($val) > 12) ? '\''.add_cs_lashes($val, '\'\\').'\'' : $val;
			if(is_array($val)) {
				$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
			} else {
				$evaluate .= "$comma$key => $val";
			}
			$comma = ",\n\r$space";
		}
	}
	$evaluate .= "\n\r$space)";
	return $evaluate;
}
?>