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
	$url='https://m.ctrip.com/webapp/hotel/j/hotellistbody?pageid=212093';
	$ua='Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
	$post='{"adultCounts":0,"checkinDate":"20171109","checkoutDate":"20171110","cityID":559,"keyword":"博雯佳宜酒店","userLongitude":0}';
	// 20171108日期格式  郑州
	$result=$curl->init($url,$post,0,0,1,$ua,array('content-type:application/json'));
	preg_match('/\<textarea class=\"hotelist_response\" style=\"display:none;\"\>(.*)\<\/textarea\>/', $result,$match);
	if($match[1])
		$result=$match[1];
	$result=json_decode($result);
	$list=$result->hotelListResponse->hotelInfoList;
	if($list){
		foreach ($list as $value) {
			echo '<pre>';
			$name=$value->shortName;
			$price=$value->price;
			// $dis=distance($value->lat,$value->lon,$lat,$lng,false);
			// if($dis<0.5)
			// 	return array($price,$name);
			// var_dump($value);
			echo $name;
		}
		return array(0,$name);
	}

		
