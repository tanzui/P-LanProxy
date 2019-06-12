# P-LanProxy

#### 介绍
基于php workerman 实现的多端口多客户端 端口转发 内网穿透工具 自带web配置界面 和api接口 纯php-cli环境即可运行

#### 软件架构
软件架构说明
基于 workerman 实现

###后台界面
![输入图片说明](https://images.gitee.com/uploads/images/2019/0612/125443_c0bbfb8b_1733513.png "屏幕截图.png")

#### 安装教程

安装了php-cli环境即可

#### 使用说明

1、服务端启动

php server.php start <-d>

2、客户端启动

php client.php start <-d>

### web配置登录地址
http://host:8080/login.php

默认账号密码 admin

config.json 文件 服务端配置 保存了服务端 ip 以及内网数据发送端口和客户端配置，后台登录账号密码，谨慎修改

示例如下


```
{
	"server_host": "123.123.123.123",
	"server_port": 4901,
	"user": {
		"username": "admin",
		"password": "21232f297a57a5a743894a0e4a801fc3"
	},
	"forward": {
		"098f6bcd4621d373cade4e832627b4f6": {
			"name": "test",
			"port": {
				"1234": {
					"local_host": "127.0.0.1",
					"local_port": "80"
				},
				"2345": {
					"local_host": "127.0.0.1",
					"local_port": "22"
				}
			}
		}
	}
}
```
server_host 服务器ip

server_port 内网发送数据到服务端的端口

username 后台配置账号

password 后台配置密码（MD5）



### 配置步骤
1、后台生成客户端配置，秘钥（标识符） 端口

2、客户端配置文件 identifier.cfg 写入后台配置的秘钥（标识符）

3、启动客户端即可

### 默认需放行端口
2206

8081

4091

### api
1、创建转发
http://127.0.0.1:8081/api?ctrl=save_forward&identifier=<秘钥（标识符）>&name=<名称>

成功返回 ok 字符串 失败返回 err 已存在返回 repeat

2、新增转发端口
http://127.0.0.1:8081/api?ctrl=save_forward&identifier=<秘钥（标识符）>&remote_port=<服务端端口>&local_host=<127.0.0.1>&local_port=<内网端口>

成功返回 ok 字符串 客户端不存在 返回 null 失败返回 err 已存在返回 repeat

3、修改转发名称

http://127.0.0.1:8081/api?ctrl=save_forward&identifier=<秘钥（标识符）>&name=<新名称>

成功返回 ok 字符串 客户端不存在 返回 null 失败返回 err

4、修改转发标识符

http://127.0.0.1:8081/api?ctrl=save_forward&identifier=<秘钥（标识符）>&newidentifier=<新的秘钥（标识符）>&name=<名称>

成功返回 ok 字符串 客户端不存在 返回 null 失败返回 err 新标识符已存在返回 repeat

5、删除转发

http://127.0.0.1:8081/api?ctrl=delete_forward&identifier=<秘钥（标识符）>

成功返回 ok 失败返回 err 不存在返回 null

6、删除转发端口
http://127.0.0.1:8081/api?ctrl=delete_forward_port&identifier=<秘钥（标识符）>&remote_port=<服务端端口>
成功返回 ok 失败返回 err 不存在返回 null

7、检测是否在线

http://127.0.0.1:8081/api?ctrl=is_online&identifier=<秘钥（标识符）>
在线 返回 online 离线 返回offline

### 说明
实验性项目，MIT开源，代码写的有点随心所欲、谨慎用于项目生产环境、后面我会慢慢优化、欢迎大家贡献代码