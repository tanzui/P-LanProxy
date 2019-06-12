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
			<li class="layui-this tab-title">添加客户端</li>
		</ul>
        <div>
			
			  <input type="text" id="name" name="name"  placeholder="请输入客户端名称" class="layui-input" value="<?=$config['forward'][$identifier]['name']?>">
			
			  <input type="text" id="identifier" name="identifier" placeholder="请输入客户端秘钥"  class="layui-input" value="<?=$identifier?>">
			  <button style="margin-top:5px;margin-bottom:5px;" class="layui-btn layui-btn-xs" onclick="GenerateIdentifier();">生成秘钥</button>
			
		</div>
		<button class="layui-btn" onclick="save_forward_port();">保存</button>
        <script src="./layui/layui.js"></script>
        <script src="./js/md5.js"></script>
        <script src="./js/jquery-3.1.1.min.js"></script>
        <script>
		
		function save_forward_port()
		{
			var url;
			if('<?=$identifier?>' == "" || '<?=$identifier?>' ==  $('#identifier').val())
			{
				url = 'save_device.php?identifier='+ $('#identifier').val() +'&name=' + $('#name').val();
			}
			else
			{
				url = 'save_device.php?identifier=<?=$identifier?>&name=' + $('#name').val() + '&newidentifier=' + $('#identifier').val();
			}
			
			$.get(url ,function(data){
					if(data == 'ok')
					{
						layer.msg('保存成功');
						window.location.reload();
					}
					else if(data = 'repeat')
					{
						layer.msg('秘钥重复');
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
		
		var chars = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
		function generateMixed(n) {
			 var res = "";
			 for(var i = 0; i < n ; i ++) {
				 var id = Math.ceil(Math.random()*35);
				 res += chars[id];
			 }
			 return res;
		}
            
		function GenerateIdentifier()
		{
			var timestamp = new Date().getTime();
			$("#identifier").val(hex_md5($("#name").val() + timestamp + generateMixed(32)));
		}
        
        </script>
    </body>
</html>