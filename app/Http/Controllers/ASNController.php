<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NIC\RIPEController;
use App\Jobs\ASSETCreate;
use App\Models\ASN;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ASNController extends Controller
{
    //每个用户限制添加ASN ASN唯一不可重复添加
    /**
     * 获取该ASN的邮箱
     * @param int $asn
     * @return array|bool
     */
    public function getASNEmail($asn)
    {
        $data = new RIPEController();
        $result = $data->rdap('autnum',$asn);
        $email = [];
        if ($result && $result['entities']){
            foreach ($result['entities'] as $item){
                if (!empty($item['vcardArray'])){
                    foreach ($item['vcardArray'] as $vcard){
                        if (is_array($vcard)){
                            foreach ($vcard as $v){
                                if (isset($v[0]) && $v[0] == "email" && isset($v[3])){
                                    $email[] = $v[3];
                                }
                            }
                        }
                    }
                }
            }
        }
        return empty($email) ? false : $email;

    }


    /**
     * 页面返回
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('ASN/Index',[
            'asn'=>$user->asn
        ]);
    }

    /**
     * 添加ASN操作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        //TODO 其他验证方式支持
        $user = Auth::user();
        Validator::make($request->toArray(), [
            'asn' => [
                'required',
                'integer',
                Rule::unique('asn')->where(function ($query){
                    return $query->where('validate', true);
                }),
                'max:2147483647',
                'min:1'
            ],
        ])->validateWithBag('storeASN');

        if ($user->asn->count() > 5){
            //无事发生,一个账户绑定大于5个ASN是想干嘛
            throw ValidationException::withMessages([
                'storeASN' => ['You can only bind 5 ASN'],
            ]);
        }
        $emails = $this->getASNEmail($request->asn);
        $validate = 0;

        if (!empty($emails) && is_array($emails) && in_array(strtolower($user->email),$emails) && isset($user->email_verified_at)){
            //用户注册时候的邮箱和ASN管理员邮箱一致 自动通过验证
            $validate=1;
            $email = $user->email;
            $email_verified_at = $user->email_verified_at;
            //通过认证则请求RIPE更新AS-SET
//            ASSETCreate::dispatch();
        }

        ASN::updateOrInsert([
            'asn'=>$request->asn,
        ],[
            'user_id'=>$user->id,//如果对象存在且满足重设条件 将会将已创建对象的所有者更新到对方
            'validate'=>$validate,
            'email'=>$email ?? null,
            'email_verified_at'=>$email_verified_at ?? null,
        ]);

        return Redirect::route('bgp.validate')->with('success', 'Add ASN Success');

    }
}
