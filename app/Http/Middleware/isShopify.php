<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class isShopify
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
        $hmac_header =   $request->header("HTTP_X_SHOPIFY_HMAC_SHA256");
        $data = file_get_contents('php://input');
        $verified = $this->verify_webhook($data, $hmac_header);
        if ($verified) {
            return $next($request);
        } else {
            http_response_code(401);
        }
    }

    public function verify_webhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, \env('KEY_SECRET_APP_SHOPIFY'), true));
        return hash_equals($hmac_header, $calculated_hmac);
    }
}
