<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class PasswordResetController extends Controller
{
    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email address
        $request->validate(['email' => 'required|email']);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a reset token
            $token = JWTAuth::fromUser($user);

            // Send the notification with the token
            $user->notify(new ResetPasswordNotification($token));
        }

        return response()->json(['message' => 'Password reset link sent if the email exists.']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Attempt to reset the password
        try {
            // Decode the JWT token to get the user
            $user = JWTAuth::setToken($request->token)->toUser();
    
            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();
    
            return response()->json(['message' => 'Password has been reset successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token or failed to reset password.'], 500);
        }
    }

}
