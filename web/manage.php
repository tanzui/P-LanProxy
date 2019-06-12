<?php 
Workerman\Protocols\Http::sessionStart();
if(!isset($_SESSION["webox_username"]))
{
    Workerman\Protocols\Http::header("location:login.php");
	Workerman\Protocols\Http::end("未登录");
}

$json = file_get_contents("../config.json");

$config = json_decode($json,true);

?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title>P-LanProxy</title>
        <link rel="stylesheet" href="./layui/css/layui.css">
        <style>
            .nav
            {
                height: 5cm;
                background-color: #23262E;
            }
        </style>
    </head>
    <body>
        
        <ul class="layui-nav layui-nav-tree layui-nav-side" lay-filter="test">
        <!-- 侧边导航: <ul class="layui-nav layui-nav-tree layui-nav-side"> -->
          <li class="layui-nav-item layui-nav-itemed">
            <a href="javascript:;">客户端管理</a>
            <dl class="layui-nav-child">
              <dd><a href="javascript:load_page('device_list.php');">客户端管理</a></dd>
              <dd><a href="javascript:load_page('device_edit.php');">添加客户端</a></dd>
            </dl>
          </li>
		  
		  <li class="layui-nav-item layui-nav-itemed">
            <a href="javascript:;">配置管理</a>
            <dl class="layui-nav-child">
				<?php
					foreach($config['forward'] as $key => $val)
					{
				?>
					<dd><a href="javascript:load_page('forward_list.php?identifier=<?=$key?>');"><?=$val['name']?></a></dd>
				<?php
					}
				?>
              
            </dl>
          </li>
          
           
          
          <li class="layui-nav-item">
            <a href="logout.php">退出</a>
          </li>
          
        </ul>
        
        <div class="layui-body">
            
        </div>
        
        <script src="./layui/layui.all.js"></script>
       
        <script src="./js/jquery-3.1.1.min.js"></script>
        <script>
		
		$(document).ready(function () {
			load_page('device_list.php');
		});
		
        //注意：导航 依赖 element 模块，否则无法进行功能性操作
        layui.use('element', function(){
          var element = layui.element;
        });
        
        function load_page(pageUrl)
        {
            $(".layui-body").load(pageUrl);
        }
        
        </script>
    </body>
</html>
