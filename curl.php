<?php
	/*
	CURL类
	*/
	class HttpHelper{
		private $ch=null;		//curl对象
		public 	$cookie_name;	//cookies路径与名称设置
		public 	$ssl=0;			//ssl-http访问链接设置
		public 	$ua; 			//user-agent设置
		public 	$header;		//头部信息设置
		function __construct(){
			$this->ch=curl_init();	
		}

		function init($url,$post='',$sc=0,$gc=0,$ssl=0,$ua=0,$header=0){
			curl_setopt($this->ch, CURLOPT_URL,$url);
			curl_setopt($this->ch, CURLOPT_HEADER,0);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_TIMEOUT,30);
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);

			// 加载设置
			if($post)
				$this->set_post(1,$post);
			else
				$this->set_post(0);

			if($this->ssl||$ssl)
				$this->set_ssl();
			if($sc&&$sc!==''){
				// $this->cookie_name=tempnam('./cookies','cookies_');
				$this->set_cookies($sc);
			}
			if($gc&&$gc!==''){
				$this->get_cookies($gc);
			}
			if($ua&&$ua!=='')
				$this->set_user_agent($ua);
			if($header)
				$this->set_header($header);
			$result=curl_exec($this->ch);
			// $this->close();
			return $result;
		}
		// 设置$post参数
		function set_post($ispost,$post=''){
			curl_setopt($this->ch,CURLOPT_POST,$ispost);
			if($post!='')
				curl_setopt($this->ch,CURLOPT_POSTFIELDS,$post);
		}
		// 设置ssl连接
		function set_ssl(){
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER,1);
			curl_setopt($this->ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		}
		// 存cookies
		function set_cookies($sc){
			curl_setopt($this->ch,CURLOPT_COOKIEJAR,$sc);
		}
		// 取cookies
		function get_cookies($gc){
			curl_setopt($this->ch,CURLOPT_COOKIEFILE,$gc);
		}
		// 设置UA字符串
		function set_user_agent($ua){
			curl_setopt($this->ch,CURLOPT_USERAGENT,$ua);
		}
		//设置header
		function set_header($header){
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,$header);
		}
		function close(){
			curl_close($this->ch);
		}
		function console_info(){
			return curl_getinfo($this->ch); 
		}
	}


