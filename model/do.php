<?
	error_reporting(E_ALL);
	define('COOKIE',dirname(dirname(__FILE__)).'/cookies/cookie_dfyoo') ;
	require_once('curl.php');	
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
				header('Location:../index.php');
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
?>