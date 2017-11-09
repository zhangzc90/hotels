<?php
	@ob_end_clean();
	ob_implicit_flush(1);
	// 接收文件
	set_time_limit(0); 
	require_once('curl.php');
	date_default_timezone_set('Asia/Shanghai');
	

	// 获取酒店列表
	function get_meituan_hotels($curl,$offset,$cityId,$sedate,$sdate,$edate){
		static $i=1;
		$url=sprintf('https://ihotel.meituan.com/hbsearch/HotelSearch?utm_medium=touch&version_name=999.9&platformid=1&cateId=20&newcate=1&limit=20&offset=%d&cityId=%d&startendday=%s&startDay=%d&endDay=%d&attr_28=129&sort=defaults&accommodationType=1&hotelStar=0,1;2,3',$offset,$cityId,$sedate,$sdate,$edate);
		$result= $curl->init($url);
		$meituan=json_decode($result);
		if(isset($meituan->data)&&$meituan->data->searchresult){
			$data=$meituan->data->searchresult;
			echo '<table border=1 cellpadding=5 cellspacing=0>';
			echo '<thead><tr><td>编号</td><td style="width:400px;">酒店名称</td><td style="width:300px;">地址</td><td>美团价格</td><td>艺龙价格</td><td>去哪儿价格</td></tr></thead>';		
			foreach ($data as $value) {
				echo '<tr>';
				$name=$value->name;
				$addr=$value->addr;
				$avgScore=$value->avgScore;
				$lat=$value->lat;
				$lng=$value->lng;
				$lowestPrice=$value->lowestPrice;
				echo sprintf('<td>%s</td><td>%s</td><td>%s</td><td>￥%d</td>',$i,$name,$addr,$lowestPrice);

				// 这里开始提取其他渠道的价格
				// 艺龙价格
				$elong=get_elong_hotels($curl,$name,1701,date('Y-m-d',strtotime($sdate)),date('Y-m-d',strtotime($edate)+60*60*24),$lat,$lng);
				echo '<td title="',$elong[1],'">￥',$elong[0],'</td>';
				// 去哪儿网价格
				$quar=get_qunar_hotels($curl,$name,'郑州',date('Y-m-d',strtotime($sdate)),date('Y-m-d',strtotime($edate)+60*60*24),$lat,$lng);
				echo '<td title="',$quar[1],'">￥',$quar[0],'</td>';
				
				///////////////////////////////////////////////////////////
				echo '</tr>';
				$i++;
			}
			echo '</table><br>';
			// 递归查询列表数据
			get_meituan_hotels($curl,$offset+20,$cityId,$sedate,$sdate,$edate);
		}
	}
	// 单点查询艺龙
	function get_elong_hotels($curl,$name,$cityId,$sdate,$edate,$lat,$lng){
		$url=sprintf('http://m.elong.com/hotel/api/list?_rt=1510041525403&keywords=%s&pageindex=0&indate=%s&outdate=%s&actionName=h5=>brand=>getHotelList&ctripToken=&elongToken=j9hw5i4a-4971-4214-96ba-b5edf227f2dd&esdnum=9400489&city=%s',$name,$sdate,$edate,$cityId);
		$result=$curl->init($url,0,0,0,0,'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1');
		if($result){
			$result=json_decode($result);
			$list=$result->hotelList;
			if($list){
				foreach ($list as $value) {
					$name=$value->hotelName;
					$price=$value->lowestPrice;
					$pt=coordinate_switch($value->baiduLatitude,$value->baiduLongitude);
					$dis=distance($pt['lat'],$pt['lng'],$lat,$lng,false);
					if($dis<0.5)
						return array($price,$name);
				}
				return 0;
			}
		}

	}
	// 去哪儿
	function get_qunar_hotels($curl,$name,$cityId,$sdate,$edate,$lat,$lng){
		$url=sprintf('http://touch.qunar.com/api/hotel/hotellist?sort=0&keywords=%s&checkInDate=%s&checkOutDate=%s&couponsSelected=-1&city=%s&page=1',$name,$sdate,$edate,$cityId);
		global $cookie_name;
		$cip = '123.125.68.'.mt_rand(0,254);
		$xip = '125.90.88.'.mt_rand(0,254);
		$result=$curl->init($url,0,0,0,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',$cookie_name,array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip));
		$result=json_decode($result);
		if($result->msg==''){			
			$list=$result->data->hotels;		
			if($list){
				foreach ($list as $value) {
					$name=$value->attrs->hotelName;
					$price=$value->price;
					$coord=$value->attrs->gpoint;
					$point=explode(',',$coord);
					// $pt=coordinate_switch($point[0],$point[1]);
					$dis=distance($point[0],$point[1],$lat,$lng,false);
					if($dis<0.5)
						return array($price,$name);
				}
				return 0;
			}
		}else{
			return '接口出错';
		}
	}
	// 途牛
	function get_tuniu_hotels($curl,$name,$cityId,$sdate,$edate,$lat,$lng){
		https://m.tuniu.com/api/hotel/API/h5?c={"ct":30,"v":"1.0.0","as":"hotelList"}&d={"page":1,"limit":10,"cityCode":1202,"checkInDate":"2017-11-09","checkOutDate":"2017-11-10","key":"圣菲特花园酒店","lowPrice":0,"highPrice":0,"stars":[],"brands":[],"poiNames":"","districtName":"","poiCodes":[],"districtCode":0,"hotelLabels":[],"facilities":[],"lat":"34.76056","lng":"113.61856","radius":0,"sort":1,"index":0,"suggest":{},"isBookable":0,"isConfirm":0}
	}

	//百度转腾讯坐标转换
	function coordinate_switch($a,$b){
	    $x = (double)$b - 0.0065;
	    $y = (double)$a - 0.006;
	    $x_pi = 3.14159265358979324;
	    $z = sqrt($x * $x+$y * $y) - 0.00002 * sin($y * $x_pi);
	 
	    $theta = atan2($y,$x) - 0.000003 * cos($x*$x_pi);
	 
	    $gb = number_format($z * cos($theta),15);
	    $ga = number_format($z * sin($theta),15);	 
	 
	    return ['lat'=>$ga,'lng'=>$gb];	 
	}
	//腾讯转百度坐标转换lat,lng
	function coordinate_switchf($a,$b){
	    $x = (double)$b ;
	    $y = (double)$a;
	    $x_pi = 3.14159265358979324;
	    $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);	 
	    $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);	 
	    $gb = number_format($z * cos($theta) + 0.0065,6);
	    $ga = number_format($z * sin($theta) + 0.006,6);	 
	    return ['Latitude'=>$ga,'Longitude'=>$gb];	 
	}
	// 坐标距离
	function distance($lat1, $lng1, $lat2, $lng2, $miles = true){
		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lng1 *= $pi80;
		$lat2 *= $pi80;
		$lng2 *= $pi80;
		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;
		$a = sin($dlat/2)*sin($dlat/2)+cos($lat1)*cos($lat2)*sin($dlng/2)*sin($dlng/2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;
		return ($miles ? ($km * 0.621371192) : $km);
	}



	// 测试代码
	$curl=new HttpHelper();
	$offset=0;
	$cityId=73;
	$sedate=date('Ymd').'~'.date('Ymd');
	$sdate=date('Ymd');
	$edate=date('Ymd');
	// 设置去哪儿网的cookies
	$cookie_name=tempnam('./cookies','cookies_');
	$cip = '123.125.68.'.mt_rand(0,254);
	$xip = '125.90.88.'.mt_rand(0,254);
	$curl->init('http://touch.qunar.com/hotel/hotellist?city=郑州',0,$cookie_name,0,0,'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip));
	//执行入口，以美团网价格为起点
	get_meituan_hotels($curl,$offset,$cityId,$sedate,$sdate,$edate);

