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
$remote_port = $_GET['remote_port'];


if(!empty($identifier) && !empty($remote_port))
{
	$ch=curl_init("http://127.0.0.1:8081/api?ctrl=delete_forward_port&identifier=$identifier&remote_port=$remote_port");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
	$ret = curl_exec($ch);
	echo $ret;
}
else
{
	echo 'err';
}