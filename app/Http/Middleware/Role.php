<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
    {
    /**Handle an incoming request.*
    @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next*/
    // public function handle(Request $request, Closure $next, $role): Response{
    //     $user = $request->user();
    //     if (!$user) {// Optionally handle the case where there's no authenticated user.
    //         return redirect('/login');}

    //     $userRole = $user->role;

    //     // If the user's role does not match the required role, redirect them
    //     if ($userRole !== $role) {
    //         return redirect("/{$userRole}/dashboard");
    //     }

    //     return $next($request);
    //     }
    }