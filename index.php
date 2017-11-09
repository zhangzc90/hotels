<?php
	require_once('curl.php');
	$curl=new HttpHelper();
	$offset=0;
	$cityId='郑州';
	$name='格尔国际酒店';
	$sdate=date('Y-m-d');
	$edate=date('Y-m-d',strtotime('+1 day'));
	// 设置去哪儿网的cookies
	$cookie_name=tempnam('./cookies','cookies_');
	$cip = '123.125.68.'.mt_rand(0,254);
	$xip = '125.90.88.'.mt_rand(0,254);
	$res=$curl->init('http://touch.qunar.com/hotel/hotellist?city=郑州',0,$cookie_name,0,0,'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip));
	$url=sprintf('http://touch.qunar.com/api/hotel/hotellist?sort=0&keywords=%s&checkInDate=%s&checkOutDate=%s&couponsSelected=-1&city=%s&page=1',$name,$sdate,$edate,$cityId);
	$result=$curl->init($url,0,0,$cookie_name,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36');
	echo '<pre>';
	$result=json_decode($result);
	if($result->msg==''){			
		$list=$result->data->hotels;		
		if($list){
			foreach ($list as $value) {
				$name=$value->attrs->hotelName;
				$price=$value->price;
				$coord=$value->attrs->gpoint;
				$point=explode(',',$coord);
				$pt=coordinate_switch($point[0],$point[1]);
				$dis=distance($pt['lat'],$pt['lng'],$lat,$lng,false);
				if($dis<0.5)
					return $price."【".$name."】";
			}
			return 0;
		}
	}else{
		return '接口出错';
	}
