<?php

namespace App\Rules;

use App\Models\ASN;
use App\Models\BGPSession;
use App\Models\Node;
use App\Models\Tunnel;
use Auth;
use Illuminate\Contracts\Validation\Rule;
use IPTools\IP;

class BGP implements Rule
{
    protected $tunnel_id;
    protected $asn_id;


    /**
     * Create a new rule instance.
     *
     * @param $asn_id
     * @param  $tunnel_id
     */
    public function __construct($asn_id, $tunnel_id)
    {
        $this->asn_id = $asn_id;
        $this->tunnel_id = $tunnel_id;
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

        $tunnels = Tunnel::where('user_id', Auth::user()->id)->join('nodes_components', function ($join) {
            $join->on('tunnels.node_id', '=', 'nodes_components.node_id')
                ->where('nodes_components.component', '=', 'FRR')
                ->where('nodes_components.status', '=', 'active');
        })->select('*','tunnels.id as tunnel_id')->get();
        $tunnels->where('id', $this->tunnel_id);
        if ($tunnels->isEmpty()) {
            return false;
        }

        $asn = ASN::find($this->asn_id);
        if ($asn == null) {
            return false;
        }
        if (!$asn->validate) {
            return false;
        }
        if ($asn->user_id != Auth::user()->id) {
            return false;
        }
        //Unique
        $bgp = BGPSession::where('tunnel_id', $this->tunnel_id)->where('asn_id', $this->asn_id)->get();
        if (!$bgp->isEmpty()) {
            return false;
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
        return 'Illegal submission';
    }
}
