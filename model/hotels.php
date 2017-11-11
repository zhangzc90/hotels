<?php
	// 封装hotel对象
	class hotels{
		public $uid;		//酒店ID
		public $name;		//酒店名称
		public $cityID;		//城市编号
		public $indate;		//入住日期
		public $outdate;	//离开日期
		public $lat;		//纬度
		public $lng;		//经度
		public $stars;		//星级
		public $price;		//最低价格
		public $address;	//酒店地址
		public $rooms;		//房间
		function __construct($name='',$cityID=0,$indate=0,$outdate=0,$stars='',$lat='',$lng='',$price=0){
			$this->name=$name;
			$this->cityID=$cityID;			
			$this->indate=($indate==0?date('Ymd'):$indate);
			$this->outdate=($outdate==0?date('Ymd'):$outdate);
			$this->lat=$lat;
			$this->lng=$lng;
			$this->stars=$stars;
			$this->price=$price;
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
	}