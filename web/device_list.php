<?php
	Workerman\Protocols\Http::sessionStart();
    if(!isset($_SESSION["webox_username"]))
	{
		Workerman\Protocols\Http::header("location:login.php");
		Workerman\Protocols\Http::end("未登录");
	}
	
	//curl get
	/*function curl($url)
	{
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//不判定证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//不判定证书
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch);
		return $output;
	}
    */
    $json = file_get_contents("../config.json");
	$config = json_decode($json,true);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <table id="table" class="layui-table">
            <tr>
                <th>客户端名称</th>
                <th>客户端密钥</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        <?php
            
            try
            {
                
                foreach ($config['forward'] as $key => $val)
                {
                    //print_r($row);
                    
         ?>
        
        
            <tr>
                <td><?php echo $val['name']; ?></td>
                <td><?php echo $key; ?></td>
                <td><?php 
					$ch=curl_init('http://127.0.0.1:8081/api?ctrl=is_online&identifier=' . $key);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
					$is_online = curl_exec($ch);
					//$is_online = curl();
					if($is_online == 'online')
					{
						echo '<span class="layui-badge layui-bg-green">在线</span>';
					}
					else
					{
						echo '<span class="layui-badge layui-bg-gray">离线</span>';
						
					}
				?></td>
                <td><button class="layui-btn layui-btn-danger layui-btn-xs" onclick="del_device('<?=$key?>');">删除</button> <button class="layui-btn layui-btn-xs" onclick="load_page('device_edit.php?identifier=<?=$key?>');">编辑</button></td>
                
            </tr>
        
        
        
        <?php
                    
                }
            }
            catch (PDOException $e)
            {
                //print_r($e);
                die("pdo err");
            }
            
        ?>
        
        </table>    
            <script>
			function load_page(pageUrl)
			{
				$(".layui-body").load(pageUrl);
			}
			
			function del_device(identifier)
			{
				layer.confirm('确定要删除么?', {icon: 3, title:'提示'}, function(index){
				  //do something
				  $.get('del_device.php?identifier=' + identifier ,function(data){
						if(data == 'ok')
						{
							layer.msg('删除成功');
							window.location.reload();
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