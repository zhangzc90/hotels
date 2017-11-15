<?php
	require_once('model/curl.php');
	require_once('model/hotels.php');
	require_once('model/control.php');
	@ob_end_clean();
	ob_implicit_flush(1);
	set_time_limit(0); 
	date_default_timezone_set('Asia/Shanghai');	
	error_reporting(E_ERROR);
?>
<!DOCTYPE html>
<html lang='zh'>
<head>
	<title>价格数据对比</title>
	<meta charset="utf-8">
	<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" rel="stylesheet">
	<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>	
	<style type="text/css">
		body{
			font-size: 14px;
		}
		.table-striped tbody tr:nth-of-type(odd){
			background: #d9edf7;
		}
		.table-hover tbody tr:hover {
			background-color: #f2dede;
			cursor: pointer;
		}
		.table td, .table th{
			padding: 0.5rem;
		}
		.price a{
			color: #d9534f;
		}
	</style>
</head>
<body>
	<div class='container'>
		<div class='panel panel-default'>
			<div class='panel-body'>
				<form class="form-inline" method="POST" onsubmit="return submitValidate()">
					<div class="form-group">
						<label class="form-label">城市：</label>
						<select id='city' class='form-control' name='city' style='width: 100px;'>
							<option value=0>请选择城市</option>
							<option value='郑州'>郑州</option>
						</select>
					</div>
					<div class="checkbox">
						<label>星级：</label>
						<label>
							<input type="checkbox" name='stars[]' value='6'>经济型
						</label>
						<label>
							<input type="checkbox" name='stars[]' value='4,5'>舒适/三星
						</label>
						<label>
							<input type="checkbox" name='stars[]' value='2,3'>高档/四星
						</label>
						<label>
							<input type="checkbox" name='stars[]' value='0,1'>豪华/五星
						</label>
					</div>
					<div class="form-group">
						<label class="form-label">酒店名称：</label>
						<input type="text" class="form-control" name='hotelName' placeholder="酒店名称">
					</div>
					<button type="submit" class="btn btn-info">搜索</button>
				</form>
			</div> 
		</div>
		<table class='table table-bordered table-striped table-hover'>
			<thead>
				<tr>
					<th>序号</th>
					<th>酒店名称</th>
					<th>地址</th>
					<th>美团</th>
					<th>笛风</th>
					<th>途牛</th>
					<th>去哪儿</th>
					<th>艺龙</th>
					<th>携程</th>
				</tr>
			</thead>
			<tbody>
			<?php
				define('COOKIE_QUNAR', tempnam('./cookies','cookies_'));
				// 酒店参数
				//酒店名称、城市id、入住时间、离开时间、星级
				$city=array('郑州'=>array('meituan'=>73,'elong'=>1701,'tuniu'=>1202,'ctrip'=>559,'qunar'=>'郑州','dfyoo'=>1202));
				if($_POST){
					// 获取页面输入参数
					$name=$_POST['hotelName'];
					$c=$city[$_POST['city']];
					$stars=@(implode(';', $_POST['stars']));
					//封装查询数据对象 
					$hotel=new hotels($name,$c,date('Ymd'),date('Ymd'),$stars);
					// 美团数据提取
					$meituan=get_meituan($hotel);
					$i=1;
					foreach ($meituan as $mhotel) {
						// 艺龙
						$ehotel=new hotels($mhotel->name,$c,date('Ymd'),date('Ymd'),$stars,$mhotel->lat,$mhotel->lng);
						$elong=get_elong($ehotel);

						// 途牛
						$thotel=new hotels($mhotel->name,$c,date('Y-m-d'),date('Y-m-d',strtotime('+1 day')),$stars,$mhotel->lat,$mhotel->lng);
						$tuniu=get_tuniu($thotel);

 						// 去哪儿	
 						$qhotel=new hotels($mhotel->name,$c,date('Y-m-d'),date('Y-m-d',strtotime('+1 day')),$stars,$mhotel->lat,$mhotel->lng);					
						$qunar=get_qunar($qhotel,COOKIE_QUNAR);

						// 携程
						$chotel=new hotels($mhotel->name,$c,date('Ymd'),date('Ymd',strtotime('+1 day')),$stars,$mhotel->lat,$mhotel->lng);		
						$ctrip=get_ctrip($chotel);
						
						// 笛风数据
						$dhotel=new hotels($mhotel->name,$c,date('Y-m-d'),date('Y-m-d',strtotime('+1 day')),$stars,$mhotel->lat,$mhotel->lng);		
						$dfyoo=get_dfyoo($dhotel);
						// 接收城市在EOF中无法转换的问题
						$qc=$c['qunar'];
						echo
<<<EOF
							<tr>
								<td>$i</td>
								<td>$mhotel->name</td>
								<td>$mhotel->address</td>
								<td class="price">
									<a target="_blank" href="http://hotel.meituan.com/$mhotel->uid">￥$mhotel->price</a>
								</td>
								<td class="price">
									<a target="_blank" href="https://www.dfyoo.com/hotel/detail/$dfyoo->uid?checkInDate=$dhotel->indate&checkOutDate=$dhotel->outdate">￥$dfyoo->price</a>
								</td>
								<td class="price">
									<a target="_blank" href="http://hotel.tuniu.com/detail/$tuniu->uid">￥$tuniu->price</a>
								</td>
								<td class="price">
									<a target="_blank" href="http://touch.qunar.com/hotel/hoteldetail?city=$qc&seq=$qunar->uid">￥$qunar->price</a>
								</td>
								<td class="price">
									<a target="_blank" href="$elong->uid">￥$elong->price</a>
								</td>
								<td class="price">
									<a target="_blank" href="http://hotels.ctrip.com/hotel/$ctrip->uid.html">$ctrip->price</a>
								</td>
							</tr>
EOF;
						$i++;
					}					
				}
			?>
			</tbody>
		</table>
	</div>
	<script type="text/javascript">
		function submitValidate(){
			var city=document.getElementById('city').value;
			if(city==0){
				alert('城市不能为空');
				return false;
			}
		}
		console.log("author:zhangzc\r\n邮箱:mr394649849@163.com\r\n");
	</script>
</body>
</html>