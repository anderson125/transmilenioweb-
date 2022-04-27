<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PermissionsChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $method = explode('@',Route::current()->action['uses']);
        $method = $method[count($method)-1];
        $controller = class_basename(Route::current()->controller);

        $action = "{$controller}@{$method}";

        if(!$request->user()->can($action)){
            return response()->json(['message'=>'Unauthorized', 'response'=>['errors'=>['El usuario no tiene acceso a esta acción.']]],401);
        }

        return $next($request);
    }
}
