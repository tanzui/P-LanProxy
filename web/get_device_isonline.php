<?php
$identifier = $_GET['identifier'];
if(!empty($identifier))
{
	$ch=curl_init('http://127.0.0.1:8081/api?ctrl=is_online&identifier=' . $identifier);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
	$is_online = curl_exec($ch);
	echo $is_online;
}
else
{
	echo 'err';
}