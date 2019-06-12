<?php
use Workerman\Worker;
use \Workerman\WebServer;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/Channel/src/Server.php';
require_once __DIR__ . '/Channel/src/Client.php';


// 初始化一个Channel服务端
$channel_server = new Channel\Server('0.0.0.0', 2206);

// tcp服务端
$worker = new Worker('tcp://0.0.0.0:4901');
$worker->name = 'tcp';
$worker->count = 1;

// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();

$worker->onError = function($connection, $err_code, $err_msg)
{
	echo "$err_code, $err_msg";
};

//保存远程客户端列表
$worker->uidConnections = array();

//保存转发端口数据
$worker->port_forward_list = array();

//注册列表
$worker->reg_list = array();

// 每个worker进程启动时
$worker->onWorkerStart = function($worker)
{
	global $worker;
	$config = file_get_contents(__DIR__ . '/config.json');
	$worker->port_forward_list = json_decode($config,true);
	
	
	// Channel客户端连接到Channel服务端
    Channel\Client::connect('1.198.7.123', 2206);
	//监听内网注册
	Channel\Client::on('sc_reg', function($event_data)use($worker){
		//echo $event_data['identifier'] . "\n";
		if(isset($worker->port_forward_list['forward'][$event_data['identifier']]))
		{
			if(!isset($worker->reg_list[$event_data['identifier']]) && isset($worker->port_forward_list['forward'][$event_data['identifier']]['port']))
			{
				//echo $worker->port_forward_list['forward'][$event_data['identifier']]['port'] . "\n";
				foreach($worker->port_forward_list['forward'][$event_data['identifier']]['port'] as $key => $val)
				{
					//监听端口
					$inner_worker = new Worker('tcp://'. $worker->port_forward_list['server_host'] .':'.$key);
					
					// 设置端口复用，可以创建监听相同端口的Worker（需要PHP>=7.0）
					//$inner_worker->reusePort = true;
					$inner_worker->onError = function($connection, $err_code, $err_msg)
					{
						echo "$err_code, $err_msg";
					};
					
					$inner_worker->onConnect = function($connection) use($event_data,$val,$worker)
					{
						//内网不在线
						if(!isset($worker->reg_list[$event_data['identifier']]))
						{
							$connection->close();
						}
						else
						{
							$connection->pauseRecv();
						
							//保存客户端连接
							$worker->uidConnections[$connection->id]['connection'] = $connection;
							//连接信息
							$connection_info = ['remote_host' => $worker->port_forward_list['server_host'],'remote_port' => $worker->port_forward_list['server_port'],'local_host' => $val['local_host'],'local_port' => $val['local_port'],'id' => $connection->id];
							//连接信息发送至内网
							Channel\Client::publish('sc_connect_' . $event_data['identifier'],$connection_info);
						}
						
					};
					
					// 执行监听。这里会报Address already in use错误
					$inner_worker->listen();
					//echo 'listen_close_'. $event_data['identifier'] . '_' . $key;
					//监听listen关闭
					Channel\Client::on('listen_close_'. $event_data['identifier'] . '_' . $key, function($data)use($event_data,$key,$inner_worker){
						
						$inner_worker->unlisten();
						
						//取消订阅
						Channel\Client::unsubscribe('listen_close_'. $event_data['identifier'] . '_' . $key);
						//echo 'listen_close_'. $event_data['identifier'] . '_' . $key;
					});
					
				}
				
				Channel\Client::on('sc_error_' . $event_data['identifier'], function($data)use($worker){
					if(isset($worker->uidConnections[$data['c_id']]))
					{
						$worker->uidConnections[$data['c_id']]['connection']->close();
						unset($worker->uidConnections[$data['c_id']]);
					}
				});
			}
			
			$worker->reg_list[$event_data['identifier']] = time();
			
		}
		
	});
	
	// 每3秒执行一次
    $time_interval = 2;
    Timer::add($time_interval, function() use($worker)
    {
        foreach($worker->reg_list as $key => $val)
		{
			//掉线
			if(time() - $val > 2)
			{
				foreach($worker->port_forward_list['forward'][$key]['port'] as $k => $v)
				{
					Channel\Client::publish('listen_close_' . $key . '_' . $k,'close');
				}
				
				//取消订阅
				Channel\Client::unsubscribe('sc_error_' . $event_data['identifier']);
				unset($worker->reg_list[$key]);
			}
			//echo $worker->reg_list[$key] . "\n";
		}
    });
	
	
	
	
	
	// 创建一个Worker监听2345端口，使用http协议通讯
	$http_worker = new Worker("http://127.0.0.1:8081");

	// 启动4个进程对外提供服务
	$http_worker->count = 4;
	
	$http_worker->reusePort = true;

	// 接收到浏览器发送的数据时回复hello world给浏览器
	$http_worker->onMessage = function($connection, $data)
	{
		global $worker;
		$event_data = $data['get'];
		if(!isset($event_data['ctrl']))
		{
			$connection->send('err');
			return;
		}
		switch($event_data['ctrl'])
		{
			//保存转发名称
			case 'save_forward':
				//是否更新标识符
				if(isset($worker->port_forward_list['forward'][$event_data['identifier']]))
				{
					if(!isset($event_data['newidentifier']))
					{
						$worker->port_forward_list['forward'][$event_data['identifier']]['name'] = $event_data['name'];
					
						//保存配置文件
						file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
						$connection->send('ok');
						
						
					}
					else
					{
							if(!isset($worker->port_forward_list['forward'][$event_data['newidentifier']]))
							{
								//修改identifier
								$tmp = $worker->port_forward_list['forward'][$event_data['identifier']];
								unset($worker->port_forward_list['forward'][$event_data['identifier']]);
								$tmp['name'] = $event_data['name'];
								$worker->port_forward_list['forward'][$event_data['newidentifier']] = $tmp;
								
								//保存配置文件
								file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
							
								foreach($worker->port_forward_list['forward'][$event_data['identifier']]['port'] as $key => $val)
								{
									echo 'listen_close_' . $event_data['identifier'] . '_' . $key;
									Channel\Client::publish('listen_close_' . $event_data['identifier'] . '_' . $key,'close');
								}
								
								//取消订阅
								Channel\Client::unsubscribe('sc_error_' . $event_data['identifier']);
								
								$connection->send('ok');
							}
							else
							{
								//标识符重复
								$connection->send('repeat');
							}
							
						
						
					}
				}
				else
				{
					$connection->send('null');
				}
				
				
				break;
				
				//创建转发
				case 'create_forward':
					if(!isset($worker->port_forward_list['forward'][$event_data['identifier']]))
					{
						$worker->port_forward_list['forward'][$event_data['identifier']]['name'] = $event_data['name'];
						//保存配置文件
						file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
						$connection->send('ok');
					}
					else
					{
						//标识符重复
						$connection->send('repeat');
					}
					break;
				
			
			//是否在线
			case 'is_online':
				//内网不在线
				if(!isset($worker->reg_list[$event_data['identifier']]))
				{
					$connection->send('offline');
				}
				else
				{
					$connection->send('online');
				}
				break;
				
				
			//删除转发
			case 'delete_forward':
				//是否存在转发
				if(isset($worker->port_forward_list['forward'][$event_data['identifier']]))
				{
					foreach($worker->port_forward_list['forward'][$event_data['identifier']]['port'] as $key => $val)
					{
						//关闭端口
						Channel\Client::publish('listen_close_' . $event_data['identifier'] . '_' . $key,'close');
					}
					
					//取消订阅
					Channel\Client::unsubscribe('sc_error_' . $event_data['identifier']);
					
					unset($worker->port_forward_list['forward'][$event_data['identifier']]);
					
					//保存配置文件
					file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
					
					$connection->send('ok');
				}
				else
				{
					$connection->send('err');
				}
				
				
				break;
			
			//新增端口
			case 'create_forward_port':
			
				if(isset($worker->port_forward_list['forward'][$event_data['identifier']]))
				{
					$is_port_repeat = false;
					
					
					
					//是否重复端口
					foreach($worker->port_forward_list['forward'] as $key => $val)
					{
						foreach($val['port'] as $k => $v)
						{
							if($k == $event_data['remote_port'])
							{
								$is_port_repeat = true;
								break;
							}
						}
						
						if($is_port_repeat) break;
					}
					
					//检查是否可绑定
					if(!check_port_bindable('0.0.0.0',$event_data['remote_port'])) $is_port_repeat = true;
					
					if(!$is_port_repeat)
					{
						//保存起来
						$worker->port_forward_list['forward'][$event_data['identifier']]['port'][$event_data['remote_port']] = array('local_host'=>$event_data['local_host'],'local_port'=>$event_data['local_port']);
						
						file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
						
						$inner_worker = new Worker('tcp://'. $worker->port_forward_list['server_host'] .':'.$event_data['remote_port']);
						
						// 设置端口复用，可以创建监听相同端口的Worker（需要PHP>=7.0）
						//$inner_worker->reusePort = true;
						$inner_worker->onError = function($connection, $err_code, $err_msg)
						{
							echo "$err_code, $err_msg";
							$connection->send('err');
						};
						
						$inner_worker->onConnect = function($connection) use($event_data,$worker)
						{
							//内网不在线
							if(!isset($worker->reg_list[$event_data['identifier']]))
							{
								$connection->close();
							}
							else
							{
								$connection->pauseRecv();
							
								//保存客户端连接
								$worker->uidConnections[$connection->id]['connection'] = $connection;
								//连接信息
								$connection_info = ['remote_host' => $worker->port_forward_list['server_host'],'remote_port' => $worker->port_forward_list['server_port'],'local_host' => $event_data['local_host'],'local_port' => $event_data['local_port'],'id' => $connection->id];
								//连接信息发送至内网
								Channel\Client::publish('sc_connect_' . $event_data['identifier'],$connection_info);
							}
						};
						
						// 执行监听。这里会报Address already in use错误
						$inner_worker->listen();
						
						//监听listen关闭
						Channel\Client::on('listen_close_'. $event_data['identifier'] . '_' . $event_data['remote_port'], function($data)use($event_data,$key,$inner_worker){
							$inner_worker->unlisten();
							//取消订阅
							Channel\Client::unsubscribe('listen_close_'. $event_data['identifier'] . '_' . $event_data['remote_port']);
							//echo 'listen_close_'. $event_data['identifier'] . '_' . $key;
						});
						
						$connection->send('ok');
					}
					else
					{
						$connection->send('repeat');
					}
				}
				else
				{
					//标识符不存在
					$connection->send('null');
				}
				
				
				break;
				
				
			//删除端口
			case 'delete_forward_port':
			
				if(isset($worker->port_forward_list['forward'][$event_data['identifier']]['port'][$event_data['remote_port']]))
				{
					Channel\Client::publish('listen_close_' . $event_data['identifier'] . '_' . $event_data['remote_port'],'close');
					
					unset($worker->port_forward_list['forward'][$event_data['identifier']]['port'][$event_data['remote_port']]);
					
					//保存配置文件
					file_put_contents(__DIR__ . '/config.json',json_encode($worker->port_forward_list));
					
					$connection->send('ok');
				}
				else
				{
					//端口不存在
					$connection->send('null');
				}
				
				break;
				
			
				
			default:
				$connection->send('err');
				break;
		}
		
		return;
		// 向浏览器发送hello world
		//$connection->send($data['get']['id']);
	};
	
	// 执行监听。这里会报Address already in use错误
	$http_worker->listen();
	
};


$worker->onMessage = function($connection,$data)
{
	global $worker;
	$data = json_decode($data,true);
	if(isset($data['c_id']))
	{
		$worker->uidConnections[$data['c_id']]['connection'] ->pipe($connection);
		$connection->pipe($worker->uidConnections[$data['c_id']]['connection']);
		$worker->uidConnections[$data['c_id']]['connection']->resumeRecv();
	}
	
	echo "local-".$connection->id."\n";
};




// 0.0.0.0 代表监听本机所有网卡，不需要把0.0.0.0替换成其它IP或者域名
// 这里监听8080端口，如果要监听80端口，需要root权限，并且端口没有被其它程序占用
$webserver = new WebServer('http://0.0.0.0:8080');
// 类似nginx配置中的root选项，添加域名与网站根目录的关联，可设置多个域名多个目录

$webserver->addRoot('0.0.0.0', __DIR__ . '/web');
// 设置开启多少进程
$webserver->count = 4;




Worker::runAll();


/**
 * 检查端口是否可以被绑定
 * @author flynetcn
 */
function check_port_bindable($host, $port, &$errno=null, &$errstr=null)
{
  $socket = stream_socket_server("tcp://$host:$port", $errno, $errstr);
  if (!$socket) {
    return false;
  }
  fclose($socket);
  unset($socket);
  return true;
}


