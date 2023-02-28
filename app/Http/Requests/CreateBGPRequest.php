<?php

namespace App\Http\Requests;

use App\Http\Controllers\TunnelController;
use App\Rules\BGP;
use App\Rules\TunnelIP;
use Illuminate\Foundation\Http\FormRequest;

class CreateBGPRequest extends FormRequest
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
            'asn' => ['required','integer','exists:asn,id',new BGP($this->asn,$this->tunnel)],
            'tunnel' => ['required','integer','exists:tunnels,id',new BGP($this->asn,$this->tunnel)],
        ];
    }

    protected $errorBag = 'CreateBGP';
}
