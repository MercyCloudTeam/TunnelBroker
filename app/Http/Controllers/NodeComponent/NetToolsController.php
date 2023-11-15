<?php

namespace App\Http\Controllers\NodeComponent;

class NetToolsController extends NodeComponentBaseController
{

    /**
     * Ping命令
     * @param $ip
     * @param $count
     * @param $size
     * @return string
     */
    public function commandPing($ip, $count = 4, $size = 56): string
    {
        return "ping -c $count -s $size {$ip}";
    }

    /**
     * Traceroute 命令
     * @param $ip
     * @param $max
     * @param $wait
     * @param $query
     * @param $source
     * @param $iface
     * @return string
     */
    public function commandTraceroute($ip, $max = 30, $wait = 1, $query = 1, $source = null, $iface = null): string
    {
        $command = "traceroute -m $max -w $wait -q $query {$ip}";
        if ($source) {
            $command .= " -s $source";
        }
        if ($iface) {
            $command .= " -i $iface";
        }
        return $command;
    }
}
