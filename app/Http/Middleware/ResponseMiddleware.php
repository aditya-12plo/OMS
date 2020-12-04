<?php

namespace App\Http\Middleware;

use Closure;

class ResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('X-Powered-By', env('APP_NAME'));
        // $response->header('Content-Type', "application/json");
        return $response;
    }
}
