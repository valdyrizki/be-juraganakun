<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockIPMiddleware
{
    public $whitelistIps = [
        '127.0.0.1',
        '95.111.200.230',
        '103.226.139.215'
    ];

    public $whitelistDomains = [
        'http://juraganakun.com',
        'http://admin.juraganakun.com',
    ];

    public function handle(Request $request, Closure $next)
    {

        // if (!in_array($request->getClientIp(), $this->whitelistIps) && !in_array($request->header('origin'), $this->whitelistDomains)) {
        //     return response()->json([
        //         'isSuccess' => false,
        //         'msg' => 'These credentials do not match our records.',
        //         'data' => 'ERROR',
        //     ], 401);
        // }

        return $next($request);
    }
}
