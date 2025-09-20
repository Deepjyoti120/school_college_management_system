<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        // $feePayment =
        // return ApiResponse::success([
        //     'token' => $token,
        //     'user' => $user,
        // ], 'Login successful');
    }
}
