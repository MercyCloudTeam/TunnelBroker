# bin/sh

ip link add TunnelBrokerIO type vrf table 100
ip link set dev TunnelBrokerIO up

#添加VRF表的最大metric的默认路由
ip route add table 100 unreachable default metric 4278198272
#把网卡加入VRF
#ip link set dev eth0 master TunnelBrokerIO

