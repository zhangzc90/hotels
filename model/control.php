<?php
	// 抓取数据信息并使用回调函数进行处理
	// param $curl对象
	// param $url 抓取地址
	// param $post 是否为post方式进行数据提交
	// param $回调函数名称
	function get_hotels($curl,$url,$post=0,$callback=''){
		$result=$curl->run($url,$post);
		if($result&&$callback)
			return call_user_func($callback,$result);
		else
			return $result;
	}
	// 美团数据处理回调函数
	function meituan($result){	
		$list=array();
		$meituan=json_decode($result);
		if(isset($meituan->data)&&$meituan->data->searchresult){
			$data=$meituan->data->searchresult;
			foreach ($data as $hs) {
				$h=new hotels();
				$h->name=$hs->name;
				$h->address=$hs->addr;
				$h->lat=$hs->lat;
				$h->lng=$hs->lng;
				$h->price=$hs->lowestPrice;
				$h->stars=$hs->hotelStar;
				$h->uid=$hs->poiid;
				$list[]= $h;
			}			
			return $list;
		}
	}

	// 获取美团列表数据
	/* 美团链接地址：'https://i.meituan.com/awp/h5/hotel/poi/deal.html?poiId='*/
	function get_meituan($curl,$hotel,&$hotel_list=array(),$offset=1){
		if($offset%10==1)
			$offset-=1;
		$url=sprintf('https://ihotel.meituan.com/hbsearch/HotelSearch?utm_medium=touch&version_name=999.9&platformid=1&cateId=20&newcate=1&limit=20&offset=%s&cityId=%s&startDay=%s&endDay=%s&attr_28=129&sort=defaults&accommodationType=1&hotelStar=%s&q=%s',$offset,$hotel->cityID['meituan'],$hotel->indate,$hotel->outdate,$hotel->stars,$hotel->name);
		$curl->ssl=1;
		$res=get_hotels($curl,$url,0,'meituan');
 		if(is_array($res)){
			$hotel_list=array_merge($hotel_list,$res);
			$offset+=20;	
			get_meituan($curl,$hotel,$hotel_list,$offset);

		}
		$curl->ssl=0;
		return $hotel_list;
	}
	// 艺龙
	function get_elong($curl,$hotel){
		$url=sprintf('http://m.elong.com/hotel/api/list?_rt=1510041525403&keywords=%s&pageindex=0&indate=%s&outdate=%s&actionName=h5=>brand=>getHotelList&ctripToken=&elongToken=j9hw5i4a-4971-4214-96ba-b5edf227f2dd&esdnum=9400489&city=%s',$hotel->name,$hotel->indate,$hotel->outdate,$hotel->cityID['elong']);	
		$result=get_hotels($curl,$url);
		if($result){
			$result=json_decode($result);
			if(isset($result->hotelList)&&$result->hotelList){
				foreach ($result->hotelList as $hs) {
					$h=new hotels();
					$h->name=$hs->hotelName;
					$h->uid=$hs->detailPageUrl;
					$h->stars=$hs->starLevel;
					$h->price=$hs->lowestPrice;
					$pt=$hotel->coordinate_switch($hs->baiduLatitude,$hs->baiduLongitude);
					$h->lat=$pt['lat'];
					$h->lng=$pt['lng'];
					$dis=$hotel->distance($pt['lat'],$pt['lng'],$hotel->lat,$hotel->lng,false);
					if($dis<0.5)
						return $h;
				}
			}
		}
		return new hotels();
	}
	// 途牛
	function get_tuniu($curl,$hotel){
		$url=sprintf('http://m.tuniu.com/api/hotel/API/h5?c={"ct":30,"v":"1.0.0","as":"hotelList"}&d={"page":1,"limit":10,"cityCode":%s,"checkInDate":"%s","checkOutDate":"%s","key":"%s","lowPrice":0,"highPrice":0,"stars":[],"poiNames":"","poiCodes":[],"districtCode":0,"radius":0,"sort":1,"index":0,"suggest":{},"isBookable":0,"isConfirm":0}',$hotel->cityID['tuniu'],$hotel->indate,$hotel->outdate,$hotel->name);
		$result=get_hotels($curl,$url);
		if($result){
			$result=json_decode($result);
			if(isset($result->data->rows)&&$result->data->rows){
				foreach ($result->data->rows as $hs) {
					$h=new hotels();
					$h->name=$hs->chineseName;
					$h->price=$hs->price;
					$h->lat=$hs->lat;
					$h->lng=$hs->lng;
					$h->uid=$hs->hotelId;
					$h->address=$hs->addressInfo;
					$h->stars=$hs->star;
					$dis=$hotel->distance($hs->lat,$hs->lng,$hotel->lat,$hotel->lng,false);
					if($dis<0.5)
						return $h;
				}
			}	
		}
		return new hotels();
	}
	// 去哪儿
	function get_qunar($curl,$hotel){
		if(!file_exists($curl->cookie_name)){
			$curl->set_cookies=1;
			$curl->run('http://touch.qunar.com/hotel/hotellist?city=郑州');
			$curl->set_cookies=0;
		}
		$cip = '139.125.'.mt_rand(0,254).'.'.mt_rand(0,254);
		$xip = '139.90.'.mt_rand(0,254).'.'.mt_rand(0,254);
		$curl->header=array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip);
		$url=sprintf('http://touch.qunar.com/api/hotel/hotellist?sort=0&keywords=%s&checkInDate=%s&checkOutDate=%s&couponsSelected=-1&city=%s&page=1',$hotel->name,$hotel->indate,$hotel->outdate,$hotel->cityID['qunar']);
		$curl->get_cookies=1;
		$result=get_hotels($curl,$url);
		$result=json_decode($result);
		if(isset($result->data->hotels)&&$result->data->hotels){
			foreach ($result->data->hotels as $hs) {
				$h=new hotels();
				$h->uid=$hs->id;
				$h->name=$hs->attrs->hotelName;
				$h->price=$hs->price;
				$coord=$hs->attrs->gpoint;
				$point=explode(',',$coord);
				$h->lat=$point[0];
				$h->lng=$point[1];
				$h->stars=$hs->attrs->dangciText;
				$h->address=$hs->attrs->hotelAddress;
				$dis=$hotel->distance($point[0],$point[1],$hotel->lat,$hotel->lng,false);
				if($dis<0.5)
					return $h;
			}
		}
		return new hotels();
	}
	// 携程
	function get_ctrip($curl,$hotel){
		$url='http://m.ctrip.com/webapp/hotel/j/hotellistbody?pageid=212093';
		$post=sprintf('{"adultCounts":0,"checkinDate":"%s","checkoutDate":"%s","cityID":%s,"keyword":"%s","userLongitude":0}',$hotel->indate,$hotel->outdate,$hotel->cityID['ctrip'],$hotel->name);
		$curl->header[]='content-type:application/json';
		$res=get_hotels($curl,$url,$post);
		preg_match('/\<textarea class=\"hotelist_response\" style=\"display:none;\"\>(.*)\<\/textarea\>/', $res,$match);
		if($match[1])
			$res=$match[1];
		$res=json_decode($res);
		$list=$res->hotelListResponse->hotelInfoList;
		if($list){
			foreach ($list as $hs) {
				$h=new hotels();
				$h->uid=$hs->hotelid;
				$h->name=$hs->shortName;
				$h->price=$hs->price;
				$h->lat=$hs->lat;
				$h->lng=$hs->lon;
				$hs->stars=$hs->medal;
				$dis=$hotel->distance($hs->lat,$hs->lon,$hotel->lat,$hotel->lng,false);
				if($dis<0.5)
					return $h;
			}
		}
		return new hotels();
	}
?>