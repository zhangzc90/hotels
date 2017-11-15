<?
	define('COOKIE',dirname(__FILE__).'/cookies/cookie_tuniu') ;	
	require_once('model/curl.php');	
	$curl=new HttpHelper();
	switch ($_GET['act']) {
		case 'code':
			header('Content-type: image/png');
			echo code($curl);
			break;
		case 'login':
			$code=$_POST['code'];
			$json= login($curl,$code);
			$res=json_decode($json);
			if($res->success){
				echo '<pre>';
				$json=get_hotels($curl);
				print_r(json_decode($json)->data->hotelInfo);
			}
			break;
	}
	// 验证码
	function code($curl){		
		$curl->set_cookies=1;
		$curl->cookie_name=COOKIE;
		$rand=mt_rand()/mt_getrandmax() * (1-0);
		$url='https://www.dfyoo.com/verification/image?t='.$rand;
		$res= $curl->run($url);
		return $res;
	}
	// 登录
	function login($curl,$code){
		$curl->get_cookies=1;	
		$curl->cookie_name=COOKIE;
		$url='https://www.dfyoo.com/login';
		$curl->ssl=1;
		$curl->set_cookies=1;		
		$post='name=144343&pwd=PT1hR0ZwYkdsdVoyeDJhbWxoYmpNME5RPT1hRw==&code='.$code;
		$res=$curl->run($url,$post);
		return $res;
	}
	function get_hotels($curl){
		$url='https://www.dfyoo.com/hotel/api/search';
		$curl->ua='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';
		$post=sprintf('cityCode=%s&checkInDate=%s&checkOutDate=%s&page=1&keyWords=%s',1202,date('Y-m-d'),date('Y-m-d',strtotime('+1 day')),'');
		$res=$curl->run($url,$post);
		return $res;
	}

?>