<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\InArray;
use Symfony\Component\HttpFoundation\Response;

class CekRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini');
        }
        return $next($request);
    }
}
