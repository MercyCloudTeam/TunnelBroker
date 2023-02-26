<p align="center"><a href="https://ngunion.com" target="_blank"><img src="http://ngunion.com/assets/img/logow.png" width="150"></a></p>

<p align="center">

</p>

~~~

2023.2.14 更新

更新至 Laravel 9
更新至 Vue3
添加 Wireguard & GRE & IPIP & VXLAN 隧道支持
添加 流量记录
优化 任务执行（简化Job、提高连接效率、sudo方式执行命令）
数据库设计变更

---

因为Laravel9和Vue3等大版本更新，导致BGP功能暂时不可用。Tunnel功能可正常使用。

"因为数据库变更及部分功能暂时不可用，官方（演示）站将进行“大版本更新（时间未定）”，
原本用户的BGP Tunnel暂不可用，且可能需要重置密码（结构更新导致）。"

---

接下来更新将修复及新增功能:

自动化BGP Tunnel
ASN自动验证 （暂时无法修复 ）
AS-SET自动更新
BGP会话状态监测
Tunnel流量记录显示

~~~

## 关于本项目 

使用Laravel框架及生态开发出来的Tunnel Broker（隧道中间人）面板

> IPV6 Tunnel 使您能够通过只有IPv4连接并支持IPv6的主机或路由器建立隧道，实现访问IPv6 Internet。

### 支持隧道

* sit(6in4) 
* wireguard 
* gre
* ipip
* vxlan

### 运行原理

> 本项目并不需要任何Agent程序，节点无需安装配置，仅限将该仓库代码部署至网站服务器即可使用(服务器上需安装基础软件FRRouting)。

网站服务器通过SSH远程到节点服务器执行命令

### 节点依赖软件包

* iproute2
* frrouting
* wireguard

## 功能

* 用户系统（基础的用户系统 【注册、登录、个人中心】，不包含工单等功能）
* 自动化配置 Wireguard SIT GRE IPIP VXLAN ... Tunnel 

（2023.2.14更新后、功能暂不可用，如需使用请切换到旧版本）
* FRRouting路由器 BGP自动化配置
* AS-SET自动更新（基于RIPE NCC RESTful API）
* ASN自动验证（仅限注册邮箱为ASN维护者邮箱时）

## 案例

TunnelBroker.io

TunnelBrokerIO是一个公益项目，由赞助商提供资源支持，MercyCloud&NGUnion负责维护及技术支持。

## 赞助商

* [OLVPS](https://olvps.com/cart.php)(AS59598)
* [MercyCloud](https://www.mercycloud.com/)(AS9886)
* [Sakura Network](https://sakura.as/)(AS9516)

咱们正在寻找赞助商，如果你有兴趣支持本项目，欢迎联系咱们。

> 维护本项目需要更多的人员、资源、如果您有兴趣提供赞助通过邮件、其他方式联系咱们。

## 须知

* 本项目还处于早期开发阶段，功能可能暂未稳定
* 本项目将会是 ” IPV6化 “ 的过渡项目
* 针对自行部署用户：
  * 本项目在开发之初并未制作任何收费盈利的设计，部署本项目并运营基本不会给您带来任何盈利
  * 管理该项目需要有一定的网络经验及一定的开发能力应对程序BUG

## 安装（自托管部署）

Docker部署(laravel sail)

基础部署

请参考：https://learnku.com/docs/laravel/9.x/deployment/9359

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

## 感谢

* Dcat Admin
* Laravel Framework
* Laravel Jetstream
* Laravel Telescope
* phpseclib

## 联系

支持：support@mercycloud.com （正常支持、服务邮箱）

NOC:  noc@mercycloud.com （网络问题）

> 这是一个开源项目，咱们回复时效可能会较长，请不要频繁发件催促及垃圾邮件。这些邮箱对咱们日常运营非常重要、Thanks

Telegram: [MercyCloud Channel(运营频道)](https://t.me/MercyCloudTips) | [NGUnion Channel(开发频道)](https://t.me/NGUnion) | [YFsama(开发者)](https://t.me/YFsama)

Discord: [MercyCloud](https://discord.gg/N8tv9Rb2Yj)

## 许可证 License

MIT

