<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotCreateTokenException;
use App\Http\Requests\StoreUserRequest;
use App\Mail\PasswordReset;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use App\Rules\ValidResetToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $response = $this->getResponse(__('apiResponse.store',['resource'=>"کاربر"]), [
            ['token' => JWTAuth::fromUser($user),
            'tokenType' => "Bearer"]
        ]);
        return \response()->json($response, $response['statusCode']);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            $response = $this->getValidationError( $validator->errors());
            return \response()->json($response, $response['statusCode']);
        }

        try {
            if (!$token = JWTAuth::attempt($request->only(['email', 'password']))) {
                return response()->json(['error' => __('apiResponse.auth-failed')], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => __('apiResponse.cant-create-token')], 500);
        }

        $user = Auth::user();
        $response = $this->getResponse(__('apiResponse.successful-login'), [
           [ 'token' => $token,
            'tokenType' => "Bearer",
            'user' => $user ]
        ]);
        return \response()->json($response, $response['statusCode']);

    }


}
