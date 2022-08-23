<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckAuthenticate
{
    /**
     * Check acces token from client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            info("check acces token ");
            if (!$store = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
            $request->store = $store;
            return $next($request);
        } catch (TokenExpiredException $e) {

            return response()->json([
                "message" => 'token_expired',
                "status" => 401
            ],401);
        } catch (TokenInvalidException $e) {

            return response()->json([
                "message" => "token_invalid",
                "status" => 401
            ],401);
        } catch (JWTException $e) {

            return response()->json([
                "message" => 'token_absent',
                "status" => 401
            ],401);
        }

        return response()->json(compact('store'));
    }
}
