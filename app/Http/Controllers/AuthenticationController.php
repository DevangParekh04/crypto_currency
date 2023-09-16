<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSend;
use App\Models\CryptoCurrency;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserHolding;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
    public function sendOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    // If validation fails, return the error response
    if ($validator->fails()) {
        return response()->json([
            'code' => 400,
            'status' => 'false',
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 400);
    }

    $email = $request->email;
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
    $expiredAt = now()->addMinutes(5); // Expiry time set to 5 minutes from the current time
    // Store OTP in the OTP table
    $otpData = Otp::create([
        'user_id' => $user->id,
        'otp' => $otp,
        'expired_at' => $expiredAt,
    ]);

    // Prepare data for the email
    $emailData = [
        'name' => $user->name,
        'email' => $email,
        'otp' => $otp,
    ];

    // Send email with user's name and OTP
    $emailSend = new EmailSend();
    $isSend = $emailSend->emailForgotPassword($emailData);
    if ($isSend) {
        return response()->json([
            'code' => 200,
            'status' => 'true',
            'message' => 'OTP sent successfully',
            'data' => [],
        ], 200);
    } else {
        return response()->json([
            'code' => 400,
            'status' => 'false',
            'message' => 'Something went wrong',
        ], 401);
    }
}

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'Invalid Email',
            ], 401);
        }

        $otpData = Otp::where('otp', $request->otp)->whereIsUsed(0)->first();

        if (!$otpData) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'Invalid OTP',
            ], 401);
        }

        // Check if OTP is expired
        $currentTime = now();
        $expiredAt = $otpData->expired_at;
        if ($currentTime > $expiredAt) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'OTP has expired',
            ], 401);
        }
        $otpData->is_used=1;
        $otpData->save();
        return response()->json([
            'code' => 200,
            'status' => 'true',
            'message' => 'OTP verified successfully',
        ], 200);
    }
    public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    // If validation fails, return the error response
    if ($validator->fails()) {
        return response()->json([
            'code' => 400,
            'status' => 'false',
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 400);
    }

    $email = $request->email;
    $password = $request->password;

    // Find the user by email
    $user = User::where('email', $email)->first();

    if (!$user) {
        return response()->json([
            'code' => 400,
            'status' => 'false',
            'message' => 'Invalid Email',
        ], 401);
    }

    // Update the user's password
    $user->password = Hash::make($password);
    $user->save();

    return response()->json([
        'code' => 200,
        'status' => 'true',
        'message' => 'Password updated successfully',
        'data' => [],
    ], 200);
}

    function getCriptoCurrency()
    {
        // for($i=1;$i<40;$i++){

            $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250&page=42&sparkline=false';
            $allData = [];

            $client = new Client();
            $fullUrl = $url;

            try {
                $response = $client->get($fullUrl);
                $data = json_decode($response->getBody(), true);
                // Append the data from the current page to the overall data array
                $allData = array_merge($allData, $data);

            } catch (Exception $e) {
                // Handle any errors that occurred during the API call
                // For example, log the error or return an error response
                return ['error' => 'An error occurred while fetching data from the API: ' . $e->getMessage()];
            }
            foreach($allData as $data){
                $CryptoCurrency = [
                    'currency_name'=>$data['name'],
                    'currency_code'=>$data['symbol'],
                    'currency_image'=>$data['image'],
                ];
                CryptoCurrency::create($CryptoCurrency);
            }
        // }
        // You can process the data as needed, e.g., return it or store it in the database
        return response()->json([
            'code' => 200,
            'status' => 'true',
        ], 200);
    }
}

