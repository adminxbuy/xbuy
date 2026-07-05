<?php

namespace App\Http\Controllers;

use App\Models\OtpVerification;
use App\Models\User;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    /**
     * Show User Login Page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    /**
     * Handle User Login Request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Check if user is suspended/banned first
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ($user->status === 'suspended' || $user->status === 'banned')) {
            return back()->withErrors([
                'email' => "Your account is {$user->status}.",
            ])->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            // Log IP and detect suspicious multiple accounts
            $ip = $request->ip();
            if ($ip && $user) {
                $user->update(['last_ip_address' => $ip]);
            }

            if ($user && $user->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Show User Registration Page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.register');
    }

    /**
     * Handle User Registration Request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'buyer',
            'status' => 'active',
            'is_email_verified' => true, // Auto verified since they signed up manually
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        // Log IP
        $ip = $request->ip();
        if ($ip) {
            $user->update(['last_ip_address' => $ip]);
        }

        return redirect('/')->with('success', 'Account created successfully. Welcome to X-Buy!');
    }

    /**
     * Handle User Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }

    /**
     * Show Forgot Password Request View.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send Verification OTP to user's email.
     */
    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');

        // Check if user account is suspended or banned
        $user = User::where('email', $email)->first();
        if ($user && ($user->status === 'suspended' || $user->status === 'banned')) {
            return back()->withErrors([
                'email' => "This account is {$user->status} and password cannot be reset.",
            ]);
        }

        // Generate 6 digit OTP code
        $otp = sprintf('%06d', mt_rand(1, 999999));

        // Create or update Otp Verification code
        OtpVerification::updateOrCreate(
            ['email' => $email, 'type' => 'reset'],
            [
                'otp' => $otp,
                'is_used' => false,
                'attempts' => 0,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]
        );

        Log::info("Web Forgot Password OTP for {$email}: {$otp}");

        try {
            Mail::to($email)->send(new \App\Mail\SystemNotificationMail('auth_otp', ['otp' => $otp]));
        } catch (\Exception $e) {
            Log::error("Failed to send web forgot password OTP email: " . $e->getMessage());
            return back()->withErrors([
                'email' => 'Could not send verification email. Please check your SMTP settings.',
            ]);
        }

        return redirect()->route('password.verify', ['email' => $email])
            ->with('success', 'Verification code has been sent to your email.');
    }

    /**
     * Show OTP Verification & Reset Form.
     */
    public function showResetPassword(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password', compact('email'));
    }

    /**
     * Verify OTP and Reset Password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        $otpVerification = OtpVerification::where('email', $email)
            ->where('type', 'reset')
            ->first();

        if (!$otpVerification) {
            return back()->withErrors(['otp' => 'No reset OTP verification request found.'])->withInput();
        }

        if ($otpVerification->is_used) {
            return back()->withErrors(['otp' => 'This code has already been used.'])->withInput();
        }

        if (Carbon::now()->gt($otpVerification->expires_at)) {
            return back()->withErrors(['otp' => 'This verification code has expired.'])->withInput();
        }

        if ($otpVerification->attempts >= 5) {
            return back()->withErrors(['otp' => 'Too many failed verification attempts. Please request a new code.'])->withInput();
        }

        if ($otpVerification->otp !== $otp) {
            $otpVerification->increment('attempts');
            return back()->withErrors(['otp' => 'Invalid OTP verification code.'])->withInput();
        }

        // Mark OTP as used
        $otpVerification->update(['is_used' => true]);

        // Reset user password
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->input('password')),
            ]);

            // Auto log in user
            Auth::login($user);
            $request->session()->regenerate();

            return redirect('/')->with('success', 'Your password has been reset successfully. You are now logged in.');
        }

        return redirect()->route('login')->with('success', 'Password reset successfully. Please log in.');
    }

    public function socialRedirect($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            return redirect()->route('login')->withErrors(['social' => 'Invalid social provider request.']);
        }

        // Check if provider is enabled in config/.env or DB SiteSetting
        $enabled = config("services.{$provider}.client_id") || SiteSetting::getVal("{$provider}_login_enabled", false);
        if (!$enabled) {
            return redirect()->route('login')->withErrors(['social' => ucfirst($provider) . ' login is currently disabled. Please configure your .env keys or admin settings.']);
        }

        $clientId = config("services.{$provider}.client_id") ?: SiteSetting::getVal("{$provider}_client_id");
        $clientSecret = config("services.{$provider}.client_secret") ?: SiteSetting::getVal("{$provider}_client_secret");

        // Check if Laravel Socialite exists
        if (class_exists('Laravel\Socialite\Facades\Socialite')) {
            // Retrieve dynamic keys to bind in Laravel config on-the-fly
            config([
                "services.{$provider}.client_id" => $clientId,
                "services.{$provider}.client_secret" => $clientSecret,
                "services.{$provider}.redirect" => url("/auth/social/{$provider}/callback"),
            ]);

            try {
                return \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect();
            } catch (\Exception $e) {
                Log::error("Socialite Redirect failed for {$provider}: " . $e->getMessage());
                return redirect()->route('login')->withErrors(['social' => 'Could not initiate social connection. Please check your credentials.']);
            }
        }

        // Fallback to sandbox ONLY if no credentials are set anywhere and Socialite is missing
        if (!$clientId) {
            Log::info("Socialite not installed. Initiating local simulator flow for {$provider}.");
            return redirect()->route('social.callback', [
                'provider' => $provider,
                'sandbox' => 'true',
                'state' => Str::random(40)
            ]);
        }

        return redirect()->route('login')->withErrors(['social' => 'Laravel Socialite driver is missing. Please wait for Composer install to complete.']);
    }

    /**
     * Handle Social Provider Callback.
     */
    public function socialCallback(Request $request, $provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            return redirect()->route('login')->withErrors(['social' => 'Invalid social provider callback.']);
        }

        $enabled = config("services.{$provider}.client_id") || SiteSetting::getVal("{$provider}_login_enabled", false);
        if (!$enabled) {
            return redirect()->route('login')->withErrors(['social' => ucfirst($provider) . ' login is currently disabled.']);
        }

        $clientId = config("services.{$provider}.client_id") ?: SiteSetting::getVal("{$provider}_client_id");
        $clientSecret = config("services.{$provider}.client_secret") ?: SiteSetting::getVal("{$provider}_client_secret");

        $socialUser = null;

        // If running in sandbox/testing simulator mode and no real credentials configured
        if ($request->query('sandbox') === 'true' && !$clientId) {
            $socialUser = (object)[
                'id' => 'sandbox_' . $provider . '_987654',
                'name' => 'Sandbox ' . ucfirst($provider) . ' User',
                'email' => 'sandbox.' . $provider . '@x-buy.in',
            ];
        } else {
            // Real Socialite flow
            if (class_exists('Laravel\Socialite\Facades\Socialite')) {
                config([
                    "services.{$provider}.client_id" => $clientId,
                    "services.{$provider}.client_secret" => $clientSecret,
                    "services.{$provider}.redirect" => url("/auth/social/{$provider}/callback"),
                ]);

                try {
                    $socialUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->user();
                } catch (\Exception $e) {
                    Log::error("Socialite callback failed for {$provider}: " . $e->getMessage());
                    return redirect()->route('login')->withErrors(['social' => 'Authentication failed or was cancelled by user.']);
                }
            } else {
                return redirect()->route('login')->withErrors(['social' => 'Laravel Socialite driver is missing. Please run composer install.']);
            }
        }

        if (!$socialUser || !$socialUser->email) {
            return redirect()->route('login')->withErrors(['social' => 'Could not retrieve email address from social account.']);
        }

        // If the user is already logged in, link the social account to the current user
        if (Auth::check()) {
            $user = Auth::user();
            $field = $provider . '_linked';
            $user->$field = true;
            $user->save();

            return redirect()->route('dashboard.settings.account')->with('success', ucfirst($provider) . ' account linked successfully.');
        }

        // Find user
        $user = User::where('email', $socialUser->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->name ?? explode('@', $socialUser->email)[0],
                'email' => $socialUser->email,
                'password' => Hash::make(Str::random(24)),
                'role' => 'buyer',
                'status' => 'active',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);
        }

        if ($user->status === 'suspended' || $user->status === 'banned') {
            return redirect()->route('login')->withErrors(['email' => "Your account is {$user->status}."]);
        }

        // Update last IP address
        $ip = $request->ip();
        if ($ip) {
            $user->update(['last_ip_address' => $ip]);
        }

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Logged in successfully via ' . ucfirst($provider));
    }
}
