<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use phpseclib3\Net\SSH2;

class NodeController extends Controller
{

    public function index()
    {
        $nodes = Node::all();
        return Inertia::render('Nodes/Index', [
            'nodes' => $nodes,
        ]);
    }

    /**
     * 连接服务器
     * @param Node $node
     * @return SSH2
     * @throws \Exception
     */
    public static function connect(Node $node)
    {

        $ssh = new SSH2($node->ip,$node->port,15);
        switch($node->login_type){
            case "password":
                $ssh->login($node->username, $node->password);
                break;
            case "rsa":
                //Todo RSA登录
//                $ssh->login($node->username,new Crypt)
                break;
        }
        if (empty($ssh) || !$ssh->isAuthenticated()) {
            //错误的密码将进入这个流程
            //不匹配的验证方式（键盘输入/没有认证）
            //ps：对于这种恶臭登入方式的服务器有什么连接的必有嘛？
            Log::info('Login Server Fail',$node->toArray());
            throw new \Exception("Password Error",'1001');
        }
            return $ssh;
    }
}
