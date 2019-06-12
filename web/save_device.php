<?php
Workerman\Protocols\Http::sessionStart();
if(!isset($_SESSION["webox_username"]))
{
    Workerman\Protocols\Http::header("location:login.php");
	Workerman\Protocols\Http::end("未登录");
}

$json = file_get_contents("../config.json");
$config = json_decode($json,true);

$identifier = $_GET['identifier'];
$newidentifier = $_GET['newidentifier'];
$name = $_GET['name'];

if(!empty($identifier) && !empty($name))
{
	if(isset($config['forward'][$identifier]))
	{
		if(isset($newidentifier))
		{
			$ch=curl_init("http://127.0.0.1:8081/api?ctrl=save_forward&identifier=$identifier&name=$name&newidentifier=$newidentifier");
		}
		else
		{
			$ch=curl_init("http://127.0.0.1:8081/api?ctrl=save_forward&identifier=$identifier&name=$name");
		}
	}
	else
	{
		$ch=curl_init("http://127.0.0.1:8081/api?ctrl=create_forward&identifier=$identifier&name=$name");
	}
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
	$ret = curl_exec($ch);
	echo $ret;
	
}
else
{
	echo 'err';
}