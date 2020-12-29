<p align="center"><a href="https://mercycloud.com" target="_blank"><img src="https://console.mercycloud.com/assets/images/logo/logo.png" width="150"></a></p>

<p align="center">

</p>

## 关于本项目 

使用Laravel框架及生态开发出来的Tunnel Broker（隧道中间人）面板

IPV6 Tunnel 使您能够通过只有IPv4连接并支持IPv6的主机或路由器建立隧道，实现访问IPv6 Internet。



> 本项目部分代码来自MercyCloud Automation自动化服务及Core核心服务

### 支持隧道

SIT 隧道（IPV6隧道）

BGP 隧道



> 开发之初做了IPIP GRE等隧道的支持，因为功能未测试完成所以针对其他隧道做了前端方面的屏蔽

## 功能

* 用户系统（基础的用户系统 【注册、登录、个人中心】，不包含工单等功能）
* 自动化配置Tunnel
* API 支持（包含DDNS API）
* 自动化隧道配置
* FRRouting路由器 BGP自动化配置
* AS-SET自动更新（基于RIPE NCC RESTful API）
* ASN自动验证（仅限注册邮箱为ASN维护者邮箱时）

## 官方站

TunnelBroker.io



TunnelBrokerIO是一个公益项目，由赞助商提供资源支持，MercyCloud负责维护及技术支持。

> 与MercyCloud账户不通用

## 赞助商

[OLVPS](https://olvps.com/cart.php)(AS59598)





> 维护本项目需要更多的人员、资源、如果您有兴趣提供赞助通过邮件、其他方式联系咱们。

## 须知

* 本项目还处于早期开发阶段，功能可能暂未稳定
* 本项目将会是 ” IPV6化 “ 的过渡项目
* 针对自行部署用户：
  * 本项目在开发之初并未制作任何收费盈利的设计，部署本项目并运营基本不会给您带来任何盈利
  * 管理该项目需要有一定的网络经验及一定的开发能力应对程序BUG

## 安装（自托管部署）

Docker部署

施工中~ 文档编写中



基础部署

请参考：https://learnku.com/docs/laravel/8.x/deployment/9359



配置项

```
#TunnelBroker同款配置
#接口前缀 tunnel将使用tun + id的方式命名 （tun1、tun2）
TUNNEL_NAME_PREFIX=tun 
#BGP IPV6 TUNNEL配置的RouteMAP in方向 过滤器
IPV6_IN_ROUTEMAP=customer 
#BGP IPV6 TUNNEL配置的RouteMAP out方向 过滤器
IPV6_OUT_ROUTEMAP=rpki
#BGP IPV4 TUNNEL配置的RouteMAP in方向 过滤器
IPV4_IN_ROUTEMAP=customer
#BGP IPV4 TUNNEL配置的RouteMAP out方向 过滤器
IPV4_OUT_ROUTEMAP=rpki
#RIPE TEST API请求
USE_RIPE_TEST_API=false
#自动生成的AS-SET名称
AS_SET=AS-TunnelBrokerIO
#技术支持对象
RIPE_TECH_C=MN12978-RIPE
#管理对象
RIPE_ADMIN_C=MN12978-RIPE
#维护者
RIPE_MNT_BY=MERCYCLOUD-MNT
#组织ID
RIPE_ORGANISATION=ORG-MA1787-RIPE
#RIPE维护者对象密码
RIPE_PASSWORD=
#BIND区域配置（未完成）
ZONE_NAME=tunnel.mercycloud.com.

```

> 本项目并不需要任何Agent程序，节点无需安装配置，仅限将该仓库代码部署至网站服务器即可使用。

## 感谢

* Dcat Admin
* Laravel Framework
* Laravel Jetstream
* Laravel Telescope
* l5-swagger
* phpseclib

## 联系

支持：support@mercycloud.com （正常支持、服务邮箱）

NOC:  noc@mercycloud.com （网络问题）

> 这是一个开源项目，咱们回复时效可能会较长，请不要频繁发件催促及垃圾邮件。这些邮箱对咱们日常运营非常重要、Thanks

## API文档

http://项目地址/api/documentation

施工中

## 许可证 License

MIT

