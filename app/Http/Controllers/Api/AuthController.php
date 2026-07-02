<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Send OTP to the specified email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'type' => 'required|in:register,login,reset,seller_apply',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $email = $request->input('email');
        $type = $request->input('type');

        // Check if user exists for login / reset
        $userExists = User::where('email', $email)->exists();
        if ($type === 'login' && !$userExists) {
            return $this->errorResponse('Account not found with this email. Please register first.', 404);
        }
        if ($type === 'register' && $userExists) {
            return $this->errorResponse('Account already exists with this email. Please login.', 400);
        }

        // Generate 6 digit OTP
        $otp = sprintf('%06d', mt_rand(1, 999999));

        // Save to database
        OtpVerification::updateOrCreate(
            ['email' => $email, 'type' => $type],
            [
                'otp' => $otp,
                'is_used' => false,
                'attempts' => 0,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send OTP via Log/Email
        Log::info("OTP for {$email} ({$type}): {$otp}");
        
        try {
            Mail::to($email)->send(new \App\Mail\SystemNotificationMail('auth_otp', ['otp' => $otp]));
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email: " . $e->getMessage());
        }

        return $this->successResponse(null, 'Verification code sent successfully to ' . $email);
    }

    /**
     * Verify OTP and login/register.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:register,login,reset,seller_apply',
            'name' => 'required_if:type,register|string|max:255',
            'phone' => 'required_if:type,register|string|max:15',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $email = $request->input('email');
        $otp = $request->input('otp');
        $type = $request->input('type');

        $otpVerification = OtpVerification::where('email', $email)
            ->where('type', $type)
            ->first();

        if (!$otpVerification) {
            return $this->errorResponse('No verification request found for this email.', 404);
        }

        if ($otpVerification->is_used) {
            return $this->errorResponse('This verification code has already been used.', 400);
        }

        if (Carbon::now()->gt($otpVerification->expires_at)) {
            return $this->errorResponse('This verification code has expired. Please request a new one.', 400);
        }

        if ($otpVerification->attempts >= 5) {
            return $this->errorResponse('Too many failed attempts. Please request a new verification code.', 400);
        }

        if ($otpVerification->otp !== $otp) {
            $otpVerification->increment('attempts');
            return $this->errorResponse('Invalid verification code.', 400);
        }

        // Mark OTP as used
        $otpVerification->update(['is_used' => true]);

        // Find or create user
        $user = User::where('email', $email)->first();

        if ($type === 'register') {
            if ($user) {
                return $this->errorResponse('User already exists.', 400);
            }

            // Create new user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $email,
                'phone' => $request->input('phone'),
                'password' => Hash::make(Str::random(16)),
                'is_email_verified' => true,
                'email_verified_at' => Carbon::now(),
                'role' => 'buyer',
                'status' => 'active',
            ]);
        }

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->status === 'suspended' || $user->status === 'banned') {
            return $this->errorResponse("Your account is {$user->status}.", 403);
        }

        // Store IP address and detect multiple accounts
        $ip = $request->ip();
        if ($ip) {
            $user->update(['last_ip_address' => $ip]);
            
            $sameIpCount = User::where('last_ip_address', $ip)->count();
            if ($sameIpCount > 1) {
                \App\Models\AdminAlert::firstOrCreate(
                    [
                        'type' => 'suspicious_activity',
                        'title' => 'Multiple Accounts Same IP Alert',
                        'message' => "Multiple accounts ({$sameIpCount}) detected using the same IP address: {$ip}.",
                        'severity' => 'medium',
                    ]
                );
            }

            // Trigger for Same IP address 3+ seller accounts
            $sellerCountSameIp = User::where('last_ip_address', $ip)
                ->whereHas('sellerProfile')
                ->count();
            if ($sellerCountSameIp >= 3) {
                \App\Models\FraudFlag::firstOrCreate(
                    [
                        'flag_type' => 'multiple_accounts_same_ip',
                        'flagged_user_id' => $user->id,
                        'status' => 'pending'
                    ],
                    [
                        'details' => [
                            'ip_address' => $ip,
                            'seller_accounts_count' => $sellerCountSameIp,
                        ]
                    ]
                );
            }
        }

        // Generate Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Authenticated successfully');
    }

    /**
     * Get logged-in user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user()->load('sellerProfile'));
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully');
    }
}
