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
			<li class="layui-this tab-title"><?=$config['forward'][$identifier]['name']?> - 代理配置</li>
		</ul>
        <table id="table" class="layui-table">
            <tr>
                <th>公网端口</th>
                <th>内网ip</th>
                <th>内网端口</th>
                <th>操作</th>
            </tr>
        <?php
            
            try
            {
                if(isset($config['forward'][$identifier]['port']))
				{
					
				
					foreach ($config['forward'][$identifier]['port'] as $key => $val)
					{
						//print_r($row);
                    
         ?>
        
        
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $val['local_host']; ?></td>
                <td><?php echo $val['local_port'];?></td>
                <td><button class="layui-btn layui-btn-xs layui-btn-danger" onclick="del_forward_port('<?=$key?>');">删除</button></td>
                
            </tr>
        
        
        
        <?php
					}      
				}
            }
            catch (PDOException $e)
            {
                //print_r($e);
                die("pdo err");
            }
            
        ?>
        
        </table> 
		<button class="layui-btn" onclick="load_page('forward_edit.php?identifier=<?=$identifier?>');">添加代理</button>
         <script src="./layui/layui.js"></script>
       
        <script src="./js/jquery-3.1.1.min.js"></script>
        <script>
        function load_page(pageUrl)
        {
            $(".layui-body").load(pageUrl);
        }
		
		function del_forward_port(remote_port)
        {
			
			layer.confirm('确定要删除么?', {icon: 3, title:'提示'}, function(index){
				  //do something
				   $.get('del_forward_port.php?identifier=<?=$identifier?>&remote_port=' + remote_port ,function(data){
						if(data == 'ok')
						{
							layer.msg('删除成功');
						}
						else
						{
							layer.msg('删除错误');
						}
					});
				  layer.close(index);
				});
			
        }
        
        </script>
    </body>
</html>