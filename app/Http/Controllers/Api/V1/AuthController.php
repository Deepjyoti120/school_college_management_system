<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $data = $request->input('data');
        if (
            empty($data['identities'][0]['identityValue']) || empty($data['idToken'])
        ) {
            return ApiResponse::error('Missing required fields', 422);
        }
        $idToken =  $data['idToken'];
        $tokenParts = explode('.', $idToken);
        if (count($tokenParts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

        $nationalPhone = $payload['national_phone_number'] ?? null;
        $countryCode = $payload['country_code'] ?? null;

        if (!$nationalPhone || !$countryCode) {
            return ApiResponse::error('Phone or country code missing in token', 422);
        }

        $user = User::firstOrCreate(
            [
                'phone' => $nationalPhone,
                'country_code' => $countryCode,
            ],
        );
        $user->update([
            'device_info' => $data
        ]);
        $user->refresh();
        if (!$token = auth('api')->setTTL(60 * 24 * 30)->login($user)) {
            return ApiResponse::error('Token creation failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
        ], 'Login successful');
    }

    public function emailLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Invalid credentials', Response::HTTP_BAD_REQUEST,);
        }
        $user = auth('api')->user();
        if (!$user->is_active) {
            auth('api')->logout();
            return ApiResponse::error('Your account is inactive. Please contact support.', Response::HTTP_FORBIDDEN);
        }
        // update fcm_token from body and 
        $user->update([
            'fcm_token' => $request->input('fcm_token'),
            'device_info' => $request->input('device_info'),
        ]);
        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
        ], 'Login successful');
    }
    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            $user = auth('api')->setToken($newToken)->user();
            return ApiResponse::success([
                'token' => $newToken,
                'user' => $user,
            ], 'Token refreshed');
        } catch (TokenExpiredException $e) {
            return ApiResponse::error('Refresh token expired', Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return ApiResponse::error('Token refresh failed', Response::HTTP_BAD_REQUEST);
        }
    }

    // public function refresh()
    // {
    //     try {
    //         $newToken = auth('api')->setTTL(10)->refresh();
    //         $user = auth('api')->setToken($newToken)->user();
    //         return ApiResponse::success([
    //             'token' => $newToken,
    //             'user' => $user,
    //         ], 'Token refreshed');
    //     } catch (TokenExpiredException $e) {
    //         return ApiResponse::error('Refresh token expired', Response::HTTP_UNAUTHORIZED);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error('Token refresh failed', Response::HTTP_BAD_REQUEST);
    //     }
    // }



    public function otpRequest()
    {
        // try {
        //     $newToken = auth('api')->refresh();
        //     $user = auth('api')->setToken($newToken)->user();
        //     return ApiResponse::success([
        //         'token' => $newToken,
        //         'user' => $user,
        //     ], 'Token refreshed');
        // } catch (TokenExpiredException $e) {
        //     return ApiResponse::error('Refresh token expired', Response::HTTP_UNAUTHORIZED);
        // } catch (\Exception $e) {
        //     return ApiResponse::error('Token refresh failed', Response::HTTP_BAD_REQUEST);
        // }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
