<?php

namespace App\Http\Requests;

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
        return [
            'mode'=>'required|in:sit,gre,ipip,ip6gre,ip6ip6',
            'node'=>'required|exists:nodes,id',
            'remote'=>['required','ip',new TunnelIP($this->get('mode'),$this->get('node'))],
            'dstport'=>'nullable|integer|max:65535|min:1024',//VXLAN 用户的可选配置
            'asn'=>'nullable|exists:asn,id',//VXLAN 用户的可选配置
        ];
    }

    protected $errorBag = 'createTunnel';
}
