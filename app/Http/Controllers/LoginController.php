<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use JWT;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuthExceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests;
use App\Http\Requests\LoginRequest;

use App\Models\Store;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'store','refresh']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {

        info("2...LoginController: login ".json_encode($request->toArray(),true));
        $getStore = $request->toArray();
info("2...LoginController: get info in request");
        $data = ([
            "password"=> $getStore['shop'],
            "myshopify_domain"=>$getStore['shop']
        ]);

        info("2...LoginController: validation ");
        $validator = Validator::make($data, [
            'myshopify_domain' => 'required',
            'password' => '',
        ]);
       info("2...done validetion ");
        if ($validator->fails()) {
            info("3...LoginController: fails");
            return response()->json($validator->errors(), 422);
        }
        info("2...check acctoken ");
        if (!$token = auth()->attempt($validator->validated())) {
            info("4...LoginController: attempt");
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        info("5...LoginController: provide access token");
        $access_Token = $this->createNewToken($token);


        if ($request->has("first_install_app")) {
            info("LoginController: first install app");
            return response([
                'data' => $access_Token,
                'first_install' => true,
                'status' => true,
            ], 200);
        }

        info("LoginController: login app");
        return response([
            'data' => $access_Token,
            'status' => true,
        ], 200);


    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $refresh = $this->createNewToken(auth()->refresh());
        return response([
            'data' => $refresh,
            'status' => true,
        ], 200);
    }
    /**
     * Get the authenticated User.
     *
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,

            // 'user' => auth()->user()
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['store' => $request->store]);
    }
}
