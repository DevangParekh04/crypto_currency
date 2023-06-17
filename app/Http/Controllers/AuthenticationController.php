<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSend;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserHolding;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Return the success response
        return response()->json([
            'code'=>200,
            'status' => 'true',
            'message' => 'User registered successfully',
            'data' => $user,
        ], 200);
    }
    public function login(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Generate a token for the authenticated user
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the success response with the token
        return response()->json([
            'code'=>200,
            'status' => 'true',
            'message' => 'Login successful',
            'data'=> $user,
            'token' => $token,
        ], 200);
    }
    public function verifyEmail(Request $request)
    {
            // Check if the email exists in the User table
        $email=auth()->user()->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'Invalid Email',
            ], 401);
        }

        // Generate OTP and set expiry time
        $otp = mt_rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(2);

        // Store OTP in the OTP table
        $otpData = Otp::create([
            'user_id' => $user->id,
            'currency_id' => $request->currency_id,
            'quanty' => $request->currency_id,
            'avrage' => $request->currency_id,
            'invested' => $request->currency_id,


        ]);

        // Prepare data for the email
        $emailData = [
            'name' => $user->name,
            'email' => $email,
            'otp' => $otp,
        ];

        // Send email with user's name and OTP
        $emailSend = new EmailSend();
        $emailSend->emailForgotPassword($emailData);

        return response()->json([
            'code' => 200,
            'status' => 'true',
            'message' => 'OTP sent successfully',
            'data' => [],
        ], 200);
    }


}

