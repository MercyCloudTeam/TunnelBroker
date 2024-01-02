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
            'gre' => 'GRE Tunnel',
            'sit' => 'SIT Tunnel (IPV4 to IPV6 Tunnel)',
            'ipip' => 'IPIP Tunnel'
        ],
        'status' => [
            1 => 'Normal',
            2 => 'Waiting for creation',//通过面板、API创建之后 开始进行IP分配参数检测 （提交创建后并不会立即检测是否符合创建条件、相反当创建条件不满足如果可以通过等待满足（IP分配完 没有人删除隧道或管理员添加IP段）则一直为等待阶段）
            3 => 'Waiting for reconstruction',
            4 => 'Creation failed',//当IP不够分配或者隧道参数错误时候则出现该状态需要人工介入 (默认是每隔6小时会将状态重新配置为2)
            5 => 'Waiting for update',//用户通过面板或API更新Tunnel IP的时候则陷入该状态 (Change操作)
            6 => 'Exception',//当计算流量时候找不到接口（通常为机器错误导致了重启或配置丢失）归类为这种情况 （不更改信息的情况下重新执行创建操作）
        ]
    ],
    'node' => [
        'status' => [
            1 => 'Normal',
            2 => 'Exception',
            3 => 'Offline',
        ],
        'bgp' => [
            'frr' => 'FRRouting',
            //BIRD以后再说
        ],
        'login_type' => [
            'password' => 'Password',
            'rsa' => 'RSA Cert'
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
