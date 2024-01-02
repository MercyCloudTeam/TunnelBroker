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
