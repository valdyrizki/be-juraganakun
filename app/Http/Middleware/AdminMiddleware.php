<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->level === "99" || Auth::user()->level === "10") {
            return $next($request);
        }

        //Validasi authenticated
        return response()->json([
            'isSuccess' => false,
            'msg' => 'These credentials do not match our records.',
            'data' => 'ERROR',
        ], 401);
    }
}
