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
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>P-LanProxy</title>
    </head>
    <body>
		<ul class="layui-tab-title site-demo-title">
			<li class="layui-this tab-title">新增代理</li>
		</ul>
        <div>
			
			  <input type="text" id="remote_port" name="remote_port"  placeholder="请输入后端端口" class="layui-input">
				
			  <input type="text" id="local_host" name="local_host" placeholder="请输入内网ip"  class="layui-input" value="127.0.0.1">
			
			  <input type="text" id="local_port" name="local_port"  placeholder="请输入内网端口" class="layui-input">
			
			
		</div>
		<button class="layui-btn" onclick="save_forward_port();">保存</button>
        <script src="./layui/layui.js"></script>
       
        <script src="./js/jquery-3.1.1.min.js"></script>
        <script>
		
		function save_forward_port()
		{
			$.get('save_forward_port.php?identifier=<?=$identifier?>&remote_port=' + $('#remote_port').val() + '&local_host=' + $('#local_host').val() + '&local_port=' + $('#local_port').val(),function(data){
				if(data == 'ok')
				{
					layer.msg('保存成功');
					load_page('forward_list.php?identifier=<?=$identifier?>');
				}
				else if(data = 'repeat')
				{
					layer.msg('端口重复');
				}
				else
				{
					layer.msg('保存失败');
				}
			});
		}
		
        function load_page(pageUrl)
        {
            $(".layui-body").load(pageUrl);
        }
        
        </script>
    </body>
</html>