<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleIsValid
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
        $user = $request->user;

        if ($user->role == "admin") {
            return $next($request);
        } else {
            $answer['msg'] = "This user does not have permission to perform this action";
        }

        return response()-> json($answer);

    }
}
