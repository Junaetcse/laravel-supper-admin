<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $response['companies'] = [];
        $response['session'] = ['access_token' => $token, 'session_last_access' => 0, 'session_start' => 0];
        $response['user_info'] = auth('api')->user();
        $response['roles'] = [];

        return response()->json($response);
    }

    public function me()
    {

        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'EMAIL_EXIST',
            ],406);
        }

        $user = User::create($request->all());
        $user['access_token'] = auth()->tokenById($user->id); 
        return \response()->json($user);
    }
}
