<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return null to avoid redirect
        if ($request->expectsJson()) {
            return null;
        }
        
        // For web requests, you can return a login route if you have one
        // return route('login');
        
        // For API-only apps, always return null
        return null;
    }
    
    /**
     * Handle unauthenticated users for API requests
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            abort(response()->json(['message' => 'Unauthenticated.'], 401));
        }
        
        parent::unauthenticated($request, $guards);
    }
}