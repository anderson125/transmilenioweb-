<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeWantJsonMiddleware
{
    /**
     * We only accept json
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if (!$request->isMethod('post')) return $next($request);

        $acceptHeader = $request->header('Accept');
        Log::info($acceptHeader);
        if ($acceptHeader != 'application/json') {
            return response()->json(['message' => 'Not Acceptable', 'errors' => ['La ruta no es autorizada al cliente.']], 406);
        }
        return $next($request);
    }
}
