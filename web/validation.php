<?php
    

/*
 * 验证账号是否正确
 */
    Workerman\Protocols\Http::sessionStart();
    //echo json_encode(array("username" => "dale","password" => "dale","flag" => 100));
    
    $usernmae = $_POST["username"];
    $password = $_POST["password"];
    
    if($usernmae == false || $password == false)
    {
        Workerman\Protocols\Http::end("账号或密码错误");
    }
	
		
	$json = file_get_contents("../config.json");

	$config = json_decode($json,true);

	//$userArr = json_decode($json,true);

	if($config['user']["username"] == $usernmae && $config['user']["password"] == md5($password))
	{
		$_SESSION["webox_username"] = $config['user']["username"];
		Workerman\Protocols\Http::header("location:manage.php");
	}
	else
	{
		echo "login err";
		Workerman\Protocols\Http::header("location:login.php");
	}
	
    
