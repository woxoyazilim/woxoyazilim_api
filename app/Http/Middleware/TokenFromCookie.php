<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Cookie'den token al ve Authorization header'a ekle (yoksa)
        if (!$request->header('Authorization')) {
            $token = $request->cookie('woxo-admin-token');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        return $next($request);
    }
}
