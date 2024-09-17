<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/auth/register",
 *     operationId="registerUser",
 *     tags={"Auth"},
 *     summary="Register a new user",
 *     description="Registers a new user and sends an email verification link.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully. Please check your email to verify your account.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User registered successfully. Please check your email to verify your account.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object")
 *         )
 *     )
 * )
 */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'User registered but email verification failed. Please try again later.'], 500);
        }
        

        return response()->json(['message' => 'User registered successfully. Please check your email to verify your account.'], 201);
    }

    /**
 * @OA\Post(
 *     path="/api/auth/login",
 *     operationId="loginUser",
 *     tags={"Auth"},
 *     summary="Login a user",
 *     description="Logs in a user and returns a JWT token.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful login",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="token_type", type="string", example="bearer"),
 *             @OA\Property(property="expires_in", type="integer", example=3600)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 */

 public function login(Request $request)
 {
     $credentials = $request->only('email', 'password');
 
     if (!$token = JWTAuth::attempt($credentials)) {
         return response()->json(['error' => 'Unauthorized'], 401);
     }
 
     return $this->respondWithToken($token);
 }
 
 protected function respondWithToken($token)
 {
     return response()->json([
         'access_token' => $token,
         'token_type' => 'bearer',
         'expires_in' => auth()->factory()->getTTL() * 60
     ]);
 }

/**
 * @OA\Post(
 *     path="/api/auth/logout",
 *     operationId="logoutUser",
 *     tags={"Auth"},
 *     summary="Logout a user",
 *     description="Logs out the user by invalidating their JWT token.",
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Successfully logged out")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to logout",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Failed to logout")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function logout(Request $request)
    {
        try {
            JWTAuth::parseToken()->invalidate();
            
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

}
