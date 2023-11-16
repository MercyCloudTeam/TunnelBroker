<?php

return [
    'code' => [
        'ERROR' => 0,
        'SUCCESS' => 1,
        'TUNNEL_TOO_MANY' => 10001,
        'ASN_NO_VALIDATE' => 10002,
    ],

    'tunnel' => [
        'type' => [
            'gre' => 'GRE隧道',
            'sit' => 'SIT隧道(IPV4 to IPV6隧道)',
            'ipip' => 'IPIP隧道'
        ],
        'status' => [
            1 => '正常',
            2 => '等待创建',//通过面板、API创建之后 开始进行IP分配参数检测 （提交创建后并不会立即检测是否符合创建条件、相反当创建条件不满足如果可以通过等待满足（IP分配完 没有人删除隧道或管理员添加IP段）则一直为等待阶段）
            3 => '等待重建',
            4 => '创建失败',//当IP不够分配或者隧道参数错误时候则出现该状态需要人工介入 (默认是每隔6小时会将状态重新配置为2)
            5 => '等待更新',//用户通过面板或API更新Tunnel IP的时候则陷入该状态 (Change操作)
            6 => '异常',//当计算流量时候找不到接口（通常为机器错误导致了重启或配置丢失）归类为这种情况 （不更改信息的情况下重新执行创建操作）
        ]
    ],
    'node' => [
        'status' => [
            1 => '正常',
            2 => '异常',
            3 => '下线',
        ],
        'bgp' => [
            'frr' => 'FRRouting',
            //BIRD以后再说
        ],
        'login_type' => [
            'password' => '账户密码登录Password',
            'rsa' => '密钥登录RSA'
        ]
    ],
    'links' => [
        'category' => [
            'sponsor' => 'Sponsor',
            'friend' => 'Friend',
            'other' => 'Other',
        ],
    ]
];
