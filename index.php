<?php
	require_once('model/curl.php');
	require_once('model/hotels.php');
	require_once('model/control.php');
	@ob_end_clean();
	ob_implicit_flush(1);
	set_time_limit(0); 
	date_default_timezone_set('Asia/Shanghai');	
	error_reporting(0);
?>
<!DOCTYPE html>
<html>
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
		.price{
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
					<th>途牛</th>
					<th>去哪儿</th>
					<th>艺龙</th>
					<th>携程</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$curl=new HttpHelper();
				// 设置头部信息
				$cip = '113.125.68.'.mt_rand(0,254);
				$xip = '115.90.88.'.mt_rand(0,254);
				$curl->header=array('CLIENT-IP:'.$cip,'X-FORWARDED-FOR:'.$xip);

				// 酒店参数
				//酒店名称、城市id、入住时间、离开时间、星级
				$city=array('郑州'=>array('meituan'=>73,'elong'=>1701,'tuniu'=>1202,'ctrip'=>559,'qunar'=>'郑州'));
				// $hotel=new hotels('',$city['郑州'],date('Ymd'),date('Ymd'),'0,1;2,3');
				if(!$_POST)
					return;
				$name=$_POST['hotelName'];
				$c=$city[$_POST['city']];
				$stars=(implode(';', $_POST['stars']));	
				$hotel=new hotels($name,$c,date('Ymd'),date('Ymd'),$stars);
				// 美团数据提取
				$meituan=get_meituan($curl,$hotel);
				$i=1;
				foreach ($meituan as $m) {
					$m->cityID=$city['郑州'];
					// 艺龙
					$m->indate=date('Ymd');
					$m->outdate=date('Ymd');
					$elong=get_elong($curl,$m);

					// 途牛
					$m->indate=date('Y-m-d',strtotime($m->indate));
					$m->outdate=date('Y-m-d',strtotime($m->outdate)+60*60*24);
					$tuniu=get_tuniu($curl,$m);

					// 去哪儿						
					$qunar=get_qunar($curl,$m);
					// 携程
					$m->indate=date('Ymd');
					$m->outdate=date('Ymd',strtotime('+1 day'));
					$ctrip=get_ctrip($curl,$m);
					echo sprintf('<tr><td>%d</td><td>%s</td><td>%s</td><td class="price">￥%s</td><td class="price">￥%s</td><td class="price">￥%s</td><td class="price">￥%s</td><td class="price">%s</td></tr>',$i++,$m->name,$m->address,$m->price,$tuniu->price,$qunar->price,$elong->price,$ctrip->price);
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
	</script>
</body>
</html>