<?php
	/*
		·CURL类
		·cookie存储路径、是否为HTTPS链接方式、header头部信息、是否设置cookie、是否读取cookie、user-agent作为开关变量存在；

		·需要抓取的连接地址与是否携带post参数需要在每次调用的时候
	*/
	class HttpHelper{
		private $ch=null;				//curl对象
		public 	$cookie_name;			//cookies路径与名称设置
		public 	$ssl=0;					//ssl-http访问链接设置		
		public 	$header;				//头部信息设置
		public 	$set_cookies=0;			//设置cookie存储cookie
		public 	$get_cookies=0;			//设置使用上一个存储的cookie
		public 	$ua='Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'; 			  //user-agent设置
		function __construct(){
			$this->ch=curl_init();
			curl_setopt($this->ch, CURLOPT_HEADER,0);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_TIMEOUT,30);
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
			// 设置头部信息
			$cip = '113.125.68.'.mt_rand(0,254);
			$xip = '115.90.88.'.mt_rand(0,254);
			$this->header=array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip);
		}
		// url与post设置需要分别设置
		function run($url,$post=''){
			curl_setopt($this->ch, CURLOPT_URL,$url);
			// 加载自定义设置
			if($post)
				$this->set_post(1,$post);
			else
				$this->set_post(0);
			if($this->ssl||$this->ssl)
				$this->set_ssl();
			if($this->set_cookies){
				if(!$this->cookie_name)
					$this->cookie_name=tempnam('./cookies','cookies_');
				$this->set_cookies();
			}
			if($this->get_cookies){
				$this->get_cookies();
			}
			if($this->ua&&$this->ua!=='')
				$this->set_user_agent($this->ua);
			if($this->header)
				$this->set_header($this->header);
			// 执行
			$result=curl_exec($this->ch);
			return $result;
		}
		// 设置$post参数
		private function set_post($ispost,$post=''){
			curl_setopt($this->ch,CURLOPT_POST,$ispost);
			if($post!='')
				curl_setopt($this->ch,CURLOPT_POSTFIELDS,$post);
		}
		// 设置ssl连接
		private function set_ssl(){
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER,1);
			curl_setopt($this->ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		}
		// 存cookies
		private function set_cookies(){
			curl_setopt($this->ch,CURLOPT_COOKIEJAR,$this->cookie_name);
		}
		// 取cookies
		private function get_cookies(){
			curl_setopt($this->ch,CURLOPT_COOKIEFILE,$this->cookie_name);
		}
		// 设置UA字符串
		private function set_user_agent(){
			curl_setopt($this->ch,CURLOPT_USERAGENT,$this->ua);
		}
		//设置header
		private function set_header(){
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,$this->header);
		}
		// 关闭当前curl连接
		function close(){
			curl_close($this->ch);
		}
		// 输出最后一次执行的信息
		function console_info(){
			return curl_getinfo($this->ch); 
		}
	}