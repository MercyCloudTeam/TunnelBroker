<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                隧道详细
            </h2>
        </template>

        <div>
            <div class=" space-y-6 max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-6 text-gray-500">
                        Tunnel ID: {{tunnel.id}} <br>
                    </div>
                    <div class="mt-6 text-gray-500">
                        服务器节点IPV4: {{tunnel.local}}<br>
                        服务器节点IPV6: {{server_ip6}}/{{tunnel.ip6_cidr}}<br>
                        用户IPV4: {{client_ip4}}<br>
                        用户IPV6: {{client_ip6}}/{{tunnel.ip6_cidr}} <br>
                        用户接入IP: {{tunnel.remote}}
                    </div>

                    <div v-if="asn" class="mt-6 text-gray-500 ">
                        您的ASN: {{asn.asn}}<br>
                        咱们的ASN: {{node.asn}}<br>
                        Peer地址:{{server_ip6}}
                    </div>
                </div>
                <div class="bg-white sm:px-20 border-b border-gray-200 ">
                    <div class="w-full p-6  md:w-1/3 px-3 ">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold " for="grid-state">
                            配置案例
                        </label>
                        <div class="relative">
                            <select @change="configurationSelect(configurationType)" v-model="configurationType" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-state">
                                <option value="ip-route">IP Route命令</option>
                                <option value="ifupdown">配置文件（Debian/Ubuntu <= 18.04）</option>
                                <option value="netplan">配置文件 Netplan（Ubuntu > 18.04）</option>
                                <option value="net-tools">Net-tools工具</option>
                                <option value="mikrotik">Mikrotik</option>
                                <option value="netbsd">NetBSD</option>
                                <option value="junos">JunOS</option>
                                <option value="ios">Cisco IOS</option>
                                <option value="windows10">Windows 10</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                    <textarea v-model="configuration" class="w-full select-all p-6  px-3 text-gray-700 border rounded-lg focus:outline-none" rows="8" placeholder="配置案例"></textarea>
                </div>

            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'

    export default {
        props: [
            'tunnel',
            'asn',
            'node',
            'client_ip4',
            'client_ip6',
            'server_ip6',
            'server_ip4',
        ],
        data() {
            return {
                configurationType: null,
                configuration:null

            }
        },

        methods: {
            configurationSelect(value){
                switch (value) {
                    case 'ip-route':
                        this.configuration = "modprobe ipv6\n" +
                            "ip tunnel add tunnelbrokerio mode sit remote "+ this.tunnel.local+" local "+ this.tunnel.remote+ "ttl "+this.tunnel.ttl +" \n"  +
                            "ip link set tunnelbrokerio up\n" +
                            "ip addr add " + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+" dev tunnelbrokerio\n" +
                            "ip route add ::/0 dev tunnelbrokerio\n" +
                            "ip -f inet6 addr"
                        break;
                    case 'ifupdown':
                        this.configuration = "auto tunnelbrokerio \n" +
                            "iface tunnelbrokerio inet6 v4tunnel \n" +
                            "   address " + this.client_ip6 + "\n" +
                            "   netmask "+ this.tunnel.ip6_cidr+" \n" +
                            "   endpoint "+ this.tunnel.local+" \n" +
                            "   local "+ this.tunnel.remote +" \n" +
                            "   gateway "+ this.server_ip6 +" \n" +
                            "   ttl "+ this.tunnel.ttl  +" \n"
                        break;
                    case 'netplan':
                        this.configuration = "network:\n" +
                            "  version: 2\n" +
                            "  tunnels:\n" +
                            "    tunnelbrokerio:\n" +
                            "      mode: sit\n" +
                            "      remote: "+ this.tunnel.local+"\n" +
                            "      local: "+ this.tunnel.remote+"\n" +
                            "      addresses:\n" +
                            "        - \"" + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+"\"\n" +
                            "      gateway6: \""+ this.server_ip6+"\""
                        break;
                    case 'net-tools':
                        this.configuration = "ifconfig sit0 up\n" +
                            "ifconfig sit0 inet6 tunnel ::"+ this.tunnel.local+"\n" +
                            "ifconfig sit1 up\n" +
                            "ifconfig sit1 inet6 add " + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+"\n" +
                            "route -A inet6 add ::/0 dev sit1"
                        break;
                    case 'mikrotik':
                        this.configuration = "/interface 6to4 add comment=\"MercyCloud TunnelBroker\" disabled=no local-address="+ this.tunnel.remote+" mtu=1280 name=sit1 remote-address="+ this.tunnel.local+"\n" +
                            "/ipv6 route add comment=\"\" disabled=no distance=1 dst-address=2000::/3 gateway="+ this.server_ip6+" scope=30 target-scope=10\n" +
                            "/ipv6 address add address=" + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+" advertise=no disabled=no eui-"+ this.tunnel.ip6_cidr+"=no interface=sit1\n"
                        break;
                    case 'netbsd':
                        this.configuration = "ifconfig gif0 create\n" +
                            "ifconfig gif0 tunnel "+ this.tunnel.remote+ " " + this.tunnel.local+"\n" +
                            "ifconfig gif0 inet6 " + this.client_ip6 + " "+ this.server_ip6+" prefixlen 128\n" +
                            "route -n add -inet6 default "+ this.server_ip6+"\n" +
                            "ifconfig gif0 up"
                        break;
                    case 'junos':
                        this.configuration = "interfaces {\n" +
                            "\tip-0/1/0 {\n" +
                            "\t\tunit 0 {\n" +
                            "\t\t\ttunnel {\n" +
                            "\t\t\t\tsource "+ this.tunnel.remote+";\n" +
                            "\t\t\t\tdestination "+ this.tunnel.local+";\n" +
                            "\t\t\t}\n" +
                            "\t\t\tfamily inet6 {\n" +
                            "\t\t\t\taddress " + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+";\n" +
                            "\t\t\t}\n" +
                            "\t\t}\n" +
                            "\t}\n" +
                            "}\n" +
                            "routing-options {\n" +
                            "\trib inet6.0 {\n" +
                            "\t\tstatic {\n" +
                            "\t\t\troute ::/0 next-hop "+ this.server_ip6+";\n" +
                            "\t\t}\n" +
                            "\t}\n" +
                            "}\n" +
                            "forwarding-options {\n" +
                            "\tfamily {\n" +
                            "\t\tinet6 {\n" +
                            "\t\t\tmode packet-based;\n" +
                            "\t\t}\n" +
                            "\t}\n" +
                            "}"
                        break;
                    case 'ios':
                        this.configuration = "configure terminal\n" +
                            "interface Tunnel0\n" +
                            " description MercyCloud TunnelBroker\n" +
                            " no ip address\n" +
                            " ipv6 enable\n" +
                            " ipv6 address " + this.client_ip6 + "/"+ this.tunnel.ip6_cidr+" \n" +
                            " tunnel source "+ this.tunnel.remote+"\n" +
                            " tunnel destination "+ this.tunnel.local+"\n" +
                            " tunnel mode ipv6ip\n" +
                            "ipv6 route ::/0 Tunnel0\n" +
                            "end\n" +
                            "write"
                        break;
                    case 'windows10':
                        this.configuration = "netsh interface teredo set state disabled\n" +
                            "netsh interface ipv6 add v6v4tunnel interface=IP6Tunnel localaddress="+ this.tunnel.remote+"  remoteaddress="+ this.tunnel.local+"\n" +
                            "netsh interface ipv6 add address interface=IP6Tunnel address= " + this.client_ip6 + "\n" +
                            "netsh interface ipv6 add route prefix=::/0 interface=IP6Tunnel nexthop="+ this.server_ip6;
                        break;
                }
            }
        },
        components: {
            AppLayout,
        },
    }
</script>
