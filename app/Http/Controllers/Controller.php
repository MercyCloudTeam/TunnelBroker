<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @OA\Info(
     *     title="MercyCloud TunnelBroker",
     *     version="1.0.0",
     *     @OA\Contact(
     *         email="support@mercycloud.com"
     *      )
     * )
     *
     */

    /**
     * 当前版本
     * @var string
     */
    public static $version = "1.0.1";

    /**
     * @param string $code
     * @param array $data
     * @param string $msg
     * @param int $httpStatus
     * @return JsonResponse
     */
    public function jsonResult($code = "ERROR", array $data = [], $msg = '', $httpStatus = 200)
    {
        return new JsonResponse([
            'data' => $data,
            'status' => config('status.code' . $code),
            'msg' => $msg
        ], $httpStatus);
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
