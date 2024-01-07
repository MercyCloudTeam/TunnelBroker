<?php

namespace App\Http\Requests;

use App\Http\Controllers\TunnelController;
use App\Rules\TunnelIP;
use Illuminate\Foundation\Http\FormRequest;

class TunnelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
//        'required|in:sit,gre,ipip,ip6gre,ip6ip6'
        $mode = implode(',',TunnelController::$availableModes);

        return [
            'mode'=>['required',"in:$mode"],
            'node'=>'required|exists:nodes,id',
            'remote'=>['required','ip',new TunnelIP($this->get('mode'),$this->get('node'))],
            'dstport'=>'nullable|integer|max:65535|min:1024',//VXLAN 用户的可选配置
            'asn'=>'nullable|exists:asn,id',//VXLAN 用户的可选配置
            'port'=>'nullable|integer|max:65535|min:1024',
            'pubkey'=>['nullable','string','regex:/^[A-Za-z0-9+\/=]{42}[A|E|I|M|Q|U|Y|c|g|k|o|s|w|4|8|0]=$/'],
            'assign_ipv4_intranet_address'=>'nullable|boolean',
            'assign_ipv4_address'=>'nullable|boolean',
        ];
    }

    protected $errorBag = 'createTunnel';
}
