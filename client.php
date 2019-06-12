<?php
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/Channel/src/Client.php';

$worker = new Worker();

$identifier = file_get_contents(__DIR__ . '/identifier.cfg');

$worker->onWorkerStart = function($worker)
{
	
	global $identifier;
	
	Channel\Client::connect('1.198.7.123', 2206);
	// 每3秒执行一次
	$time_interval = 1;
	Timer::add($time_interval, function() use($identifier)
	{
		$data = ['identifier' => $identifier];
		Channel\Client::publish('sc_reg',$data);
		echo $identifier . "\n";
	});
	
	$worker->onWorkerStart = function($worker) use($identifier)
	{
		
	};

	
	
	// 订阅connect事件，并注册事件回调
	Channel\Client::on('sc_connect_' . $identifier, function($event_data)use($worker,$identifier){
		
		//服务端连接
		$remote_con = new AsyncTcpConnection('tcp://' . $event_data['remote_host'] . ':' . $event_data['remote_port']);
		
		$remote_con->onError = function($connection, $err_code, $err_msg)use($event_data)
		{
			echo "$err_code, $err_msg";
			$data = ['c_id' => $event_data['id']];
			Channel\Client::publish('sc_error_' . $identifier,$data);
		};
		
		$remote_con->onConnect = function($remote_connection) use($event_data,$identifier)
		{
			
			$data = array('c_id' => $event_data['id']);
			$remote_connection->send(json_encode($data));
			
			//本地
			$local_con = new AsyncTcpConnection('tcp://' . $event_data['local_host'] . ':' . $event_data['local_port']);
			//$local_con->transport = 'ssl';
			$local_con->onError = function($connection, $err_code, $err_msg) use($event_data,$identifier)
			{
				echo "$err_code, $err_msg";
				$data = ['c_id' => $event_data['id']];
				Channel\Client::publish('sc_error_' . $identifier,$data);
			};
			//本地连接成功
			$local_con->onConnect = function($connection) use($remote_connection)
			{
				$remote_connection->pipe($connection);
				$connection->pipe($remote_connection);
			};
			
			$local_con->connect();
			
		};
		
		$remote_con->connect();
		
		
	});
	
	
	
};



Worker::runAll();

