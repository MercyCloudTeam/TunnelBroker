<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $managerToken = env('MANAGER_TOKEN', null);

        if ($managerToken == $request->get('token')) {
            return $next($request);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Manager token does not exist in .env/config',
        ]);
    }
}
