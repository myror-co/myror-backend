<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; 
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'email|required|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);
        $validatedData['name'] = $validatedData['email'];
        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData)->sendEmailVerificationNotification();

        return response()->json(['message' => 'User successfully created, an email has been sent to your email'], 201);
    }

    /**
     * Login
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($loginData)) 
        {
            return response()->json(['message' => 'Invalid Credentials'], 400);
        }

        if (!Auth::user()->hasVerifiedEmail())
        {
            return response()->json(['message' => "Email not verified."], 401);
        }

        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * Logout
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return response()->json(['message' => 'Logout successfully'], 200);
    }


    /**
     * Verify Email
     *
     * @return \Illuminate\Http\Response
     */
    public function verify($user_id, Request $request) 
    {
        if (!$request->hasValidSignature()) 
        {
            return response()->json(['message' => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) 
        {
            $user->markEmailAsVerified();
        }
        return redirect()->away(env('APP_FRONT_LOGIN'));;
        // return response()->json(['message' => 'Email successfully verified'], 200);
    }


    /**
     * Resend Verification Email
     *
     * @return \Illuminate\Http\Response
    */
    public function resend() 
    {
        if (Auth::user()->hasVerifiedEmail()) 
        {
            return response()->json(['message' => "Email already verified."], 400);
        }

        Auth::user()->sendEmailVerificationNotification();

        return response()->json(['message' => "Email verification link sent to your email."], 200);
    }

    /**
     * Forgot Password Request
     *
     * @return \Illuminate\Http\Response
    */
    public function forgot(Request $request) 
    {
        $credentials = $request->validate([
            'email' => 'required|email'
        ]);

        Password::sendResetLink($credentials);

        return response()->json(['message' => 'Reset password link sent to your email.'], 200);
    }

    /**
     * Reset Password
     *
     * @return \Illuminate\Http\Response
    */
    public function reset(Request $request) 
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|confirmed'
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(['message' => "Invalid token provided"], 400);
        }

        return response()->json(['message' => "Password has been successfully changed"]);
    }

}
