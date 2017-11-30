<?php
class Object extends DBAccess{
	public static $version='JMS 3.0';
	public $actionTemplate;
	public $debugLevel=0;
	public $logFileName='log/jms-server-custom.log';
	private $sysLogFileName='log/jms-system.log';
	public function display(){
		$args=func_get_args();
		$argc=func_num_args();
		if($argc==0) throw new Exception('Template is null');
		$__tplfile=$this->actionTemplate .$args[0];
		unset($args[0]);
		
		if($argc>=2){
			$__expire=$args[1];
			unset($args[1]);
			$argc-=2;
			$args=array_values($args);
			if($__expire>0){
				// 启用缓存
				$__cacheFile=$this->getCacheDir().md5($__tplfile.serialize($args));
				if(is_file($__cacheFile) && filemtime($__cacheFile)+$__expire>$this->time){
					// 缓存有效时，直接读缓存
					readfile($__cacheFile);
				}else{
					ob_start();
					include $__tplfile;
					$content=ob_get_flush();
					file_put_contents($__cacheFile, $content);
					return;
				}
			}
		}else{
			$argc=0;
		}
		
		include $__tplfile;
	}
	
	public function error($message, $isExit=null){
		header('X-Error-Message: '.rawurlencode($message));
		if($isExit===true) die;
	}
	
	public function debug($message, $error_level=9){
		var_dump($error_level);
		var_dump($this->debugLevel);
		var_dump($error_level<$this->debugLevel);
		if($error_level<$this->debugLevel) return;
		try{
			//file_put_contents($this->logFileName, date('[Y-m-d H:i:s] ', $this->time).var_export($message,true)."\r\n", FILE_APPEND);
		}catch(Exception $e){
			//file_put_contents($this->sysLogFileName, date('[Y-m-d H:i:s] ', $this->time)."写用户日志文件{$this->debugLevel}出错：".$e->getmessage()."\r\n", FILE_APPEND);
		}
	}

	/**
	 * 获取来访IP地址
	 */
	public static final function ip($outFormatAsLong=false){
		if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']))
			$ip = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
		elseif (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']))
			$ip = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
		elseif (isset($HTTP_SERVER_VARS['REMOTE_ADDR']))
			$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = '0.0.0.0';
		if(strrpos(',',$ip)>=0){
			$ip=explode(',',$ip,2);
			$ip=current($ip);
		}
		return $outFormatAsLong?ip2long($ip):$ip;
	}
	
	/**
	 * 把对象转换为数组
	 * 可以转换xml对象，使xml转换成数组
	 */
	public static final function obj2arr($o){
		if(is_object($o)) $o=get_object_vars($o);
		if(is_array($o)) foreach ($o as $k => $v) $o[$k] = jms_obj2arr($v);
		return $o;
	}
	
	public static final function iff($if, $true, $false=''){
		return $if?$true:$false;
	}
	
	public static final function ifs(){
		$args=func_get_args();
		$numargs = func_num_args();
		for($i=0; $i<$numargs; $i++){
			if($args[$i]==='0' || $args[$i]) return $args[$i];
		}
	}
	
	public static function CsubStr($str,$start,$len,$suffix='...'){
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info);
		$len*=2;
		$i=0;
		$tmpstr = '';
		while($i < $len && array_key_exists($start,$info[0])) {
			if (strlen($info[0][$start]) > 1) {
				$i+=2;
				if ($i <= $len)  $tmpstr .= $info[0][$start];
			}else {
				if (++$i <= $len)  $tmpstr .= $info[0][$start];
			}
			$start++;
		}
		return array_key_exists($start,$info[0]) ? $tmpstr.=$suffix : $tmpstr;
	}
	
	/**
	 * 添加日志
	 */
	public static final function log($obj, $file=null){
	}
}
error_reporting(E_ERROR & ~E_NOTICE);
?>