<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        
        if (Auth::check()) {
           
            if (Auth::user()->is_role == 0) {
                
                return $next($request);
            } else {
               
                Auth::logout();
                return redirect()->route('login'); 
            }
        }

        
        Auth::logout();
        return redirect()->route('login');
    }
}
