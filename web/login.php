<?php
Workerman\Protocols\Http::sessionStart();
if(isset($_SESSION["webox_username"]))
{
    Workerman\Protocols\Http::header("location:manage.php");
    
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>login</title>
        <link rel="stylesheet" href="./layui/css/layui.css">
    </head>
    <style>
        .wrapper
        {
            background: #393D49 !important;
            position: absolute;
            opacity:0.8;
            width: 100%;
            height: 100%;
            overflow:hidden;
        }
        .container
        {
            width: 18%;
            text-align: center;
            padding-left: 41%;
            padding-top: 10%;
        }
        .submit
        {
            width: 100%;
        }
        input
        {
            margin-top: 0.2cm;
        }
    </style>
    <body>
        
        <div class="wrapper">
            <div class="container">
                <h1>P-LanProxy</h1>
                <form class="layui-form" action="validation.php" method="POST">
                    <input type="text" name="username" required  lay-verify="required" placeholder="请输入账号" autocomplete="no" class="layui-input">
                    <input type="password" name="password" required lay-verify="required" placeholder="请输入密码" autocomplete="no" class="layui-input">
                    <input type="submit" value="登录" class="layui-btn submit">
                </form>
            </div>
        </div>
        
        <script src="./layui/layui.js"></script>
        <script>
        
        </script> 
    </body>
</html>
