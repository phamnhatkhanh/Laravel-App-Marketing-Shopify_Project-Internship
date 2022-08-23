<?php

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

if (!function_exists('getStoreID')) {
    /**
     * * Decode token and get id store.
     *
     * @return int
     */
    function getStoreID()
    {
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();
        $storeID = $payload['sub'];

        return $storeID;
    }
}
