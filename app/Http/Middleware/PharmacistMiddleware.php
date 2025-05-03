<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class PharmacistMiddleware 
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            
            if (Auth::user()->is_role == 2) {
                return $next($request);
            } else {
                
                Auth::logout();
                return redirect()->route('login');
            }
        } else {
  
            return redirect()->route('login');
        }
    }
}
