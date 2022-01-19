<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class TokenIsValid
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
        if ($request->has('api_token')) {
            
            // Compruebo que exista un usuario
            $apiToken = $request->input('api_token');
            $user = User::where('api_token', $apiToken)->first();

            // Si no existe lo notificamos, si existe, guardamos el usuario en el request
            if (!$user) {
                $answer['msg'] = "User doesn´t exist";
            } else {
                $request->user = $user;
                return $next($request);
            }
        } else{
            $answer['msg'] = "Api token is empty";
        }
        return response()-> json($answer);
    }
}
