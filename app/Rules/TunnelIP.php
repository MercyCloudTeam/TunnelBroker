<?php

namespace App\Rules;

use App\Models\Node;
use App\Models\Tunnel;
use Illuminate\Contracts\Validation\Rule;
use IPTools\IP;

class TunnelIP implements Rule
{
    protected $mode;
    protected $node_id;


    /**
     * Create a new rule instance.
     *
     * @param string $mode
     * @param  $node_id
     */
    public function __construct($mode,$node_id)
    {
        $this->mode = $mode;
        $this->node_id = $node_id;
    }

    //验证IP在该节点唯一
    //验证IP类型

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws \Exception
     */
    public function passes($attribute, $value)
    {
        //允许192.168内网IP创建Tunnel 但是得杀掉提交 多播ip 保留ip的提交
        if (!filter_var($value, FILTER_VALIDATE_IP,  FILTER_FLAG_NO_RES_RANGE)){
            return false;
        }
        //检测IP类型
        switch ($this->mode){
            case 'sit':
            case 'ipip6':
            case 'ipip':
            case 'gre'://只支持V4作为Remote的隧道
                $ip = new IP($value);
                if ($ip->version == "IPv6"){
                    return false;
                }
                break;
            case 'ip6gre':
            case 'ip6ip6':
                $ip = new IP($value);
                if ($ip->version == "IPv4"){
                    return false;
                }
                break;
            case 'wireguard':
            case 'vxlan':
                break;
            default:
                return false;
        }

        if (!Tunnel::where([
            ['remote','=',$value],
            ['node_id','=',(int) $this->node_id]
        ])->get()->isEmpty()){
            return false;//同一节点只能有一个
        }

        $node =  Node::find($this->node_id);
        if ($node === null){
            return false;//节点不存在
        }
        if ($value === $node->ip || $value === $node->ip6){
            return false;//不能是本节点IP
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'IP address error (incorrectly formatted/used/illegally submitted)';
    }
}
