<?php

namespace App\Static;

class Code
{
    public function __construct()
    {

    }

    public static $code = [
        'ERROR'=>0,
        'SUCCESS'=>1,
        'TUNNEL_TOO_MANY'=>10001,
        'ASN_NO_VALIDATE'=>10002,
    ];
}
