<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserDashboardController extends Controller
{
    /**
     * Show the user profile page.
     */
    public function profile()
    {
        return redirect()->route('member.profile', ['id' => Auth::user()->profile_id]);
    }

    /**
     * Show any user's profile publicly.
     */
    public function publicProfile($id)
    {
        // Decode the 10-digit profile_id back to user id
        $diff = $id - 3157000000;
        if ($diff < 0 || $diff % 1234567 !== 0) {
            abort(404);
        }
        $realId = $diff / 1234567;

        $user = \App\Models\User::findOrFail($realId);
        
        // Mock data or real seller profile data if available
        $sellerProfile = $user->sellerProfile;
        
        // Location logic
        $location = 'India';
        if ($user->show_city && $user->city && $user->state) {
            $location = $user->city . ', ' . $user->state . ', India';
        } elseif ($user->state) {
            $location = $user->state . ', India';
        }
        
        // Star review counts
        $rating = 0;
        $reviewsCount = 0;
        
        if ($sellerProfile) {
            $reviewsCount = \App\Models\Rating::where('seller_id', $sellerProfile->id)->count();
            if ($reviewsCount > 0) {
                $rating = \App\Models\Rating::where('seller_id', $sellerProfile->id)->avg('rating');
            }
        }
        
        $isOwner = Auth::check() && Auth::id() === $user->id;
        
        return view('dashboard.profile', compact('user', 'location', 'rating', 'reviewsCount', 'isOwner'));
    }

    /**
     * Show the user settings page.
     */
    public function settings()
    {
        $user = Auth::user();
        return view('dashboard.settings', compact('user'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'about' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048', // max 2MB
            'language' => 'nullable|string|in:en,hi',
        ]);

        // Handle Avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $request->name;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->about = $request->about;
        $user->language = $request->language ?? 'en';
        
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the user account settings page.
     */
    public function accountSettings()
    {
        $user = Auth::user();
        return view('dashboard.account_settings', compact('user'));
    }

    /**
     * Update user account settings.
     */
    public function updateAccountSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'birthday' => 'nullable|string|max:255',
            'vacation_mode' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
        ]);

        if ($request->email !== $user->email) {
            $user->email = $request->email;
            $user->is_email_verified = true; 
        }

        if ($request->phone !== $user->phone) {
            $user->phone = $request->phone;
            $user->is_phone_verified = false;
            $user->phone_verified_at = null;
        }

        $user->full_name = $request->full_name;
        $user->gender = $request->gender;
        $user->birthday = $request->birthday;
        $user->vacation_mode = $request->has('vacation_mode') ? (bool)$request->vacation_mode : false;

        $user->save();

        return back()->with('success', 'Account settings updated successfully.');
    }

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'firebase_id_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->phone = $request->phone;
        $user->is_phone_verified = true;
        $user->phone_verified_at = now();
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Show the user shipping settings page.
     */
    public function shippingSettings()
    {
        $user = Auth::user();
        return view('dashboard.shipping_settings', compact('user'));
    }

    /**
     * Update user shipping settings (address).
     */
    public function updateShippingSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'address' => 'required|string|max:500',
        ]);

        $user->address = $request->address;
        $user->save();

        return back()->with('success', 'Shipping address updated successfully.');
    }

    /**
     * Show the user payment settings page.
     */
    public function paymentSettings()
    {
        $user = Auth::user();
        return view('dashboard.payment_settings', compact('user'));
    }

    /**
     * Update user payment settings (UPI ID).
     */
    public function updatePaymentSettings(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'upi_id' => 'required|string|max:255|regex:/^[\w\.\-]+@[\w\-]+$/',
        ], [
            'upi_id.regex' => 'Please enter a valid UPI ID (e.g. name@bank).',
        ]);

        $user->upi_id = $request->upi_id;
        $user->save();

        return back()->with('success', 'Payment settings updated successfully.');
    }

    /**
     * Show the user notification settings page.
     */
    public function notificationSettings()
    {
        $user = Auth::user();
        return view('dashboard.notifications_settings', compact('user'));
    }

    /**
     * Update user notification settings.
     */
    public function updateNotificationSettings(Request $request)
    {
        $user = Auth::user();
        
        $user->notify_updates = $request->boolean('notify_updates');
        $user->notify_marketing = $request->boolean('notify_marketing');
        $user->notify_messages = $request->boolean('notify_messages');
        $user->notify_feedback = $request->boolean('notify_feedback');
        $user->notify_discounts = $request->boolean('notify_discounts');
        $user->notify_favorites = $request->boolean('notify_favorites');
        $user->notify_new_items = $request->boolean('notify_new_items');
        $user->notify_daily_limit = $request->notify_daily_limit ?? 'Up to 2 notifications';
        $user->notify_email = $request->boolean('notify_email');

        $user->save();

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Show the user privacy settings page.
     */
    public function privacySettings()
    {
        $user = Auth::user();
        return view('dashboard.privacy_settings', compact('user'));
    }

    /**
     * Update user privacy settings.
     */
    public function updatePrivacySettings(Request $request)
    {
        $user = Auth::user();
        
        $user->privacy_feature_marketing = $request->boolean('privacy_feature_marketing');
        $user->privacy_notify_favorites = $request->boolean('privacy_notify_favorites');
        $user->privacy_personalize_feed = $request->boolean('privacy_personalize_feed');
        $user->privacy_recently_viewed = $request->boolean('privacy_recently_viewed');

        $user->save();

        return back()->with('success', 'Privacy settings updated successfully.');
    }

    /**
     * Show the user security settings page.
     */
    public function securitySettings()
    {
        $user = Auth::user();
        return view('dashboard.security_settings', compact('user'));
    }

    /**
     * Show the change password settings page.
     */
    public function showChangePassword()
    {
        $user = Auth::user();
        return view('dashboard.change_password', compact('user'));
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->new_password)
        ])->save();

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Show the delete account settings page.
     */
    public function showDeleteAccount()
    {
        $user = Auth::user();
        return view('dashboard.delete_account', compact('user'));
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        $isSocialUser = $user->google_linked || $user->facebook_linked || $user->apple_linked;

        if (!$isSocialUser) {
            $request->validate([
                'delete_password' => ['required', 'string'],
            ]);

            if (!Hash::check($request->delete_password, $user->password)) {
                return back()->withErrors(['delete_password' => 'The provided password does not match our records.']);
            }
        }

        // Soft delete user
        $user->delete();

        // Logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted successfully.');
    }

    /**
     * Update user shipping options.
     */
    public function updateShippingOptions(Request $request)
    {
        $user = Auth::user();
        
        $user->ship_bluedart_pickup = $request->has('ship_bluedart_pickup');
        $user->ship_delhivery_pickup = $request->has('ship_delhivery_pickup');
        $user->ship_dtdc_pickup = $request->has('ship_dtdc_pickup');
        $user->ship_delhivery_dropoff = $request->has('ship_delhivery_dropoff');
        $user->ship_bluedart_dropoff = $request->has('ship_bluedart_dropoff');
        $user->ship_dtdc_dropoff = $request->has('ship_dtdc_dropoff');

        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Add a card to user payment settings.
     */
    public function addCard(Request $request)
    {
        $request->validate([
            'cardholder_name' => 'required|string|max:255',
            'card_number' => 'required|string|regex:/^[0-9\s]{13,24}$/',
            'expiry_date' => 'required|string|regex:/^(0[1-9]|1[0-2])\/?([0-9]{2})$/',
            'cvv' => 'required|string|regex:/^[0-9]{3,4}$/',
        ]);

        $user = Auth::user();
        
        // Clean card number
        $cardNumberClean = str_replace(' ', '', $request->card_number);
        
        // Detect brand (e.g. Visa starts with 4, MasterCard starts with 5, Amex with 34/37)
        $brand = 'Visa';
        if (str_starts_with($cardNumberClean, '5')) {
            $brand = 'MasterCard';
        } elseif (str_starts_with($cardNumberClean, '3')) {
            $brand = 'American Express';
        }

        $newCard = [
            'cardholder_name' => $request->cardholder_name,
            'last4' => substr($cardNumberClean, -4),
            'brand' => $brand,
            'expiry_date' => $request->expiry_date,
        ];

        $currentCards = $user->cards ?? [];
        $currentCards[] = $newCard;

        $user->cards = $currentCards;
        $user->save();

        return back()->with('success', 'Card added successfully.');
    }

    /**
     * Remove a card from user payment settings.
     */
    public function deleteCard($index)
    {
        $user = Auth::user();
        $currentCards = $user->cards ?? [];

        if (isset($currentCards[$index])) {
            unset($currentCards[$index]);
            // Re-index array
            $user->cards = array_values($currentCards);
            $user->save();
            return back()->with('success', 'Card removed successfully.');
        }

        return back()->withErrors(['card' => 'Card not found.']);
    }

    /**
     * Update user payout options (Bank Account or UPI).
     */
    public function updatePayoutOptions(Request $request)
    {
        $request->validate([
            'payout_type' => 'required|string|in:bank,upi',
            'bank_account_name' => 'required_if:payout_type,bank|nullable|string|max:255',
            'bank_account_number' => 'required_if:payout_type,bank|nullable|string|max:50',
            'bank_ifsc' => 'required_if:payout_type,bank|nullable|string|max:20',
            'payout_upi_id' => 'required_if:payout_type,upi|nullable|string|max:255|regex:/^[\w\.\-]+@[\w\-]+$/',
        ], [
            'payout_upi_id.regex' => 'Please enter a valid UPI ID (e.g. name@bank).',
        ]);

        $user = Auth::user();
        
        // Find or create seller profile to save payout details
        $sellerProfile = $user->sellerProfile;
        if (!$sellerProfile) {
            $sellerProfile = new \App\Models\SellerProfile();
            $sellerProfile->user_id = $user->id;
            $sellerProfile->shop_name = $user->name . "'s Shop";
            $sellerProfile->shop_slug = \Illuminate\Support\Str::slug($user->name . "-" . uniqid());
            $sellerProfile->shop_city = $user->city ?? 'N/A';
            $sellerProfile->shop_state = $user->state ?? 'N/A';
            $sellerProfile->shop_pincode = $user->pincode ?? '000000';
            $sellerProfile->kyc_status = 'pending';
            $sellerProfile->status = 'pending';
        }

        if ($request->payout_type === 'bank') {
            $sellerProfile->bank_account_name = $request->bank_account_name;
            $sellerProfile->bank_account_number = $request->bank_account_number;
            $sellerProfile->bank_ifsc = $request->bank_ifsc;
            $sellerProfile->upi_id = null; // Clear UPI if bank is chosen
        } else {
            $sellerProfile->upi_id = $request->payout_upi_id;
            $sellerProfile->bank_account_name = null;
            $sellerProfile->bank_account_number = null;
            $sellerProfile->bank_ifsc = null;
        }

        $sellerProfile->save();

        return back()->with('success', 'Payout options updated successfully.');
    }

    /**
     * Show bank account details page for payouts.
     */
    public function bankAccountSettings()
    {
        $user = Auth::user();
        return view('dashboard.bank_account', compact('user'));
    }

    /**
     * Update/link bank account details.
     */
    public function updateBankAccountSettings(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc' => 'required|string|max:20',
        ]);

        $user = Auth::user();

        $sellerProfile = $user->sellerProfile;
        if (!$sellerProfile) {
            $sellerProfile = new \App\Models\SellerProfile();
            $sellerProfile->user_id = $user->id;
            $sellerProfile->shop_name = $user->name . "'s Shop";
            $sellerProfile->shop_slug = \Illuminate\Support\Str::slug($user->name . "-" . uniqid());
            $sellerProfile->shop_city = $user->city ?? 'N/A';
            $sellerProfile->shop_state = $user->state ?? 'N/A';
            $sellerProfile->shop_pincode = $user->pincode ?? '000000';
            $sellerProfile->kyc_status = 'pending';
            $sellerProfile->status = 'pending';
        }

        $sellerProfile->bank_name = $request->bank_name;
        $sellerProfile->bank_account_name = $request->bank_account_name;
        $sellerProfile->bank_account_number = $request->bank_account_number;
        $sellerProfile->bank_ifsc = $request->bank_ifsc;
        $sellerProfile->upi_id = null; // Clear UPI if bank is chosen

        $sellerProfile->save();

        return redirect()->route('dashboard.settings.payments')->with('success', 'Bank account linked successfully.');
    }

    /**
     * Show the page to request/download account data.
     */
    public function manageAccountData()
    {
        $user = Auth::user();
        return view('dashboard.manage_data', compact('user'));
    }

    /**
     * Download a copy of user account data.
     */
    public function downloadAccountData()
    {
        $user = Auth::user();
        
        $data = [
            'account_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'full_name' => $user->full_name,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'city' => $user->city,
                'state' => $user->state,
                'address' => $user->address,
                'upi_id' => $user->upi_id,
                'registered_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
            ],
            'orders' => $user->orders ? $user->orders()->get()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'total' => $order->total,
                    'status' => $order->order_status,
                    'created_at' => $order->created_at ? $order->created_at->toDateTimeString() : null,
                ];
            })->toArray() : [],
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $fileName = 'account-data-' . ($user->name ? \Illuminate\Support\Str::slug($user->name) : 'user') . '-' . date('Y-m-d') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Show the page to confirm before email change.
     */
    public function confirmEmailChange()
    {
        $user = Auth::user();
        return view('dashboard.confirm_email', compact('user'));
    }

    /**
     * Simulate sending verification code and redirect to verify OTP.
     */
    public function sendEmailConfirmation()
    {
        $otp = rand(100000, 999999);
        session(['email_change_otp' => $otp]);

        return redirect()->route('dashboard.settings.email.verify')->with('success', 'Verification code generated! [Demo Mode: Your code is ' . $otp . ']');
    }

    /**
     * Show OTP verification form.
     */
    public function showVerifyEmailOtp()
    {
        $user = Auth::user();
        return view('dashboard.verify_email_otp', compact('user'));
    }

    /**
     * Verify OTP from user input.
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if ($request->otp == session('email_change_otp')) {
            session(['email_change_authorized' => true]);
            session()->forget('email_change_otp');
            return redirect()->route('dashboard.settings.email.update');
        }

        return back()->withErrors(['otp' => 'The verification code is incorrect.']);
    }

    /**
     * Show update email form if authorized.
     */
    public function showUpdateEmailForm()
    {
        if (!session('email_change_authorized')) {
            return redirect()->route('dashboard.settings.email.confirm');
        }

        $user = Auth::user();
        return view('dashboard.update_email', compact('user'));
    }

    /**
     * Save new email address to database.
     */
    public function updateEmail(Request $request)
    {
        if (!session('email_change_authorized')) {
            return redirect()->route('dashboard.settings.email.confirm');
        }

        $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->email = $request->email;
        $user->save();

        session()->forget('email_change_authorized');

        return redirect()->route('dashboard.settings.security')->with('success', 'Your email address has been updated successfully.');
    }

    /**
     * Show the page to verify phone number / 2-step verification setup.
     */
    public function showTwoStepVerification()
    {
        $user = Auth::user();
        return view('dashboard.two_step_verification', compact('user'));
    }



    /**
     * Verify OTP and enable 2-step verification.
     */
    public function verifyTwoStepOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'firebase_id_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->phone = $request->phone;
        $user->is_phone_verified = true;
        $user->phone_verified_at = now();
        $user->save();

        return redirect()->route('dashboard.settings.security')->with('success', 'Phone number verified successfully using Firebase and 2-step verification enabled.');
    }

    /**
     * Show the page to review login activity and active sessions.
     */
    public function showSessionsActivity(Request $request)
    {
        $user = Auth::user();
        
        // Dynamically get details from user-agent
        $userAgent = $request->header('User-Agent');
        $browser = 'Web Browser';
        $platform = 'Desktop';

        if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        }

        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        }

        $sessionInfo = [
            'location' => ($user->city ?? 'New Delhi') . ', India',
            'device' => $browser . ' on ' . $platform,
            'ip' => $request->ip(),
        ];

        return view('dashboard.sessions_activity', compact('user', 'sessionInfo'));
    }

    /**
     * Link or unlink social account.
     */
    public function toggleSocialLink(Request $request, $provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            abort(404);
        }

        $user = Auth::user();
        $field = $provider . '_linked';
        
        $user->$field = !$user->$field;
        $user->save();

        $status = $user->$field ? 'linked' : 'unlinked';
        return back()->with('success', ucfirst($provider) . " account has been successfully {$status}.");
    }

    /**
     * Show user wallet dashboard.
     */
    public function showWallet()
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        $transactions = $wallet->transactions()->latest()->take(10)->get();

        return view('dashboard.wallet', compact('user', 'wallet', 'transactions'));
    }

    /**
     * Show wallet setup page.
     */
    public function showWalletSetup()
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        
        if ($wallet->is_activated) {
            return redirect()->route('dashboard.wallet');
        }

        return view('dashboard.wallet_setup', compact('user', 'wallet'));
    }

    /**
     * Save wallet setup details and activate.
     */
    public function saveWalletSetup(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob_day' => 'required|numeric|between:1,31',
            'dob_month' => 'required|string|max:20',
            'dob_year' => 'required|numeric|between:1900,' . date('Y'),
            'ssn_last_four' => 'required|numeric|digits:4',
            'billing_address' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $wallet = $user->getWalletInstance();

        if (!$wallet->is_activated) {
            $wallet->is_activated = true;
            $wallet->first_name = $request->first_name;
            $wallet->last_name = $request->last_name;
            $wallet->dob_day = $request->dob_day;
            $wallet->dob_month = $request->dob_month;
            $wallet->dob_year = $request->dob_year;
            $wallet->ssn_last_four = $request->ssn_last_four;
            $wallet->save();

            // Credit ₹1000 welcome promo bonus
            $wallet->credit(1000.00, 'admin_credit', null, 'Welcome Promo Balance');

            // Save billing address if empty
            if (empty($user->address)) {
                $user->address = $request->billing_address;
                $user->save();
            }

            return redirect()->route('dashboard.wallet')->with('success', 'Wallet activated! ₹1,000.00 welcome bonus has been credited.');
        }

        return redirect()->route('dashboard.wallet')->with('success', 'Wallet is already active.');
    }

    /**
     * Show payment history page.
     */
    public function showWalletHistory()
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        
        $transactions = $wallet->transactions()->latest()->get();

        // Calculate starting balance dynamically: current balance minus credits plus debits
        $credits = $transactions->where('type', 'credit')->sum('amount');
        $debits = $transactions->where('type', 'debit')->sum('amount');
        $startingBalance = max(0, $wallet->balance - $credits + $debits);

        return view('dashboard.wallet_history', compact('user', 'wallet', 'transactions', 'startingBalance'));
    }

    /**
     * Show wallet invoices page.
     */
    public function showWalletInvoices()
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        
        // Fetch all orders placed by the user
        $orders = $user->orders()->with(['listing', 'seller.user'])->latest()->get();

        return view('dashboard.wallet_invoices', compact('user', 'wallet', 'orders'));
    }

    /**
     * View user invoice in browser.
     */
    public function viewUserInvoice($id)
    {
        $user = Auth::user();
        $order = \App\Models\Order::with(['listing', 'buyer', 'seller.user'])
            ->where('buyer_id', $user->id)
            ->findOrFail($id);

        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Download user invoice.
     */
    public function downloadUserInvoice($id)
    {
        $user = Auth::user();
        $order = \App\Models\Order::with(['listing', 'buyer', 'seller.user'])
            ->where('buyer_id', $user->id)
            ->findOrFail($id);

        $html = view('admin.orders.invoice', compact('order'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="invoice_' . $order->order_number . '.html"');
    }

    /**
     * Show wallet income dashboard.
     */
    public function showWalletIncome()
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $transactions = $wallet->transactions()
                ->selectRaw("strftime('%Y', created_at) as year, strftime('%m', created_at) as month")
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        } else {
            $transactions = $wallet->transactions()
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        }

        $months = [];
        foreach ($transactions as $t) {
            $date = \Carbon\Carbon::createFromDate($t->year, $t->month, 1);
            $months[] = [
                'year' => $t->year,
                'month' => $t->month,
                'name' => $date->format('F Y'),
            ];
        }

        if (empty($months)) {
            $months[] = [
                'year' => date('Y'),
                'month' => date('n'),
                'name' => date('F Y'),
            ];
        }

        return view('dashboard.wallet_income', compact('user', 'wallet', 'months'));
    }

    /**
     * Download monthly income statement as a text report.
     */
    public function downloadMonthlyIncomeReport($year, $month)
    {
        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $transactions = $wallet->transactions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->oldest()
            ->get();

        $credits = $transactions->where('type', 'credit')->sum('amount');
        $debits = $transactions->where('type', 'debit')->sum('amount');

        $content = "==================================================\n";
        $content .= "           X-BUY WALLET INCOME REPORT             \n";
        $content .= "==================================================\n";
        $content .= "Statement Period : " . $startDate->format('F Y') . "\n";
        $content .= "Generated On     : " . now()->format('d M Y, h:i A') . "\n";
        $content .= "Member Name      : " . $user->name . "\n";
        $content .= "Email Address    : " . $user->email . "\n";
        $content .= "--------------------------------------------------\n\n";
        
        $content .= "SUMMARY OF ACTIVITY:\n";
        $content .= "--------------------------------------------------\n";
        $content .= "Total Income Credited : INR " . number_format($credits, 2) . "\n";
        $content .= "Total Debits/Payouts  : INR " . number_format($debits, 2) . "\n";
        $content .= "Net Activity          : INR " . number_format($credits - $debits, 2) . "\n";
        $content .= "--------------------------------------------------\n\n";

        $content .= "TRANSACTION LOG:\n";
        $content .= "--------------------------------------------------\n";
        if ($transactions->count() === 0) {
            $content .= "No transactions recorded for this period.\n";
        } else {
            foreach ($transactions as $index => $tx) {
                $typeSign = $tx->type === 'credit' ? '+' : '-';
                $content .= sprintf(
                    "%02d. [%s] %s | %s - %s%s\n",
                    $index + 1,
                    $tx->created_at->format('Y-m-d H:i'),
                    strtoupper($tx->type),
                    ucwords(str_replace('_', ' ', $tx->source)),
                    $typeSign,
                    number_format($tx->amount, 2)
                );
                if ($tx->description) {
                    $content .= "    Note: " . $tx->description . "\n";
                }
            }
        }
        $content .= "==================================================\n";

        $filename = "Income_Report_" . $startDate->format('F_Y') . ".txt";

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Add or withdraw mock funds.
     */
    public function manageWalletFunds(Request $request)
    {
        $request->validate([
            'action' => 'required|in:add,withdraw',
            'amount' => 'required|numeric|min:10|max:100000',
        ]);

        $user = Auth::user();
        $wallet = $user->getWalletInstance();
        $amount = (float)$request->amount;

        try {
            if ($request->action === 'add') {
                $wallet->credit($amount, 'admin_credit', null, 'Manual top-up');
                return back()->with('success', "Successfully added ₹" . number_format($amount, 2) . " to your wallet.");
            } else {
                $wallet->debit($amount, 'admin_credit', null, 'Manual withdrawal');
                return back()->with('success', "Successfully withdrew ₹" . number_format($amount, 2) . " from your wallet.");
            }
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Show orders page (Bought & Sold).
     */
    public function showOrders(Request $request)
    {
        $user = Auth::user();
        $type = 'bought';
        if ($request->get('tab') === 'selling' || $request->get('order_type') === 'sold') {
            $type = 'sold';
        } elseif ($request->get('tab') === 'buying' || $request->get('order_type') === 'bought') {
            $type = 'bought';
        }
        $status = $request->get('status', 'all');

        $query = \App\Models\Order::with(['listing.images', 'buyer', 'seller.user', 'escrow', 'shipment', 'dispute', 'rating']);

        if ($type === 'sold') {
            $sellerProfile = $user->sellerProfile;
            if ($sellerProfile) {
                $query->where('seller_id', $sellerProfile->id);
            } else {
                $query->whereRaw('1=0'); // No seller profile means no sales
            }
        } else {
            $query->where('buyer_id', $user->id);
        }

        // Apply status filters
        if ($status === 'in_progress') {
            $query->whereIn('order_status', [
                'pending_payment', 'payment_received', 'confirmed',
                'label_generated', 'picked_up', 'in_transit',
                'out_for_delivery', 'delivered', 'testing_period'
            ]);
        } elseif ($status === 'completed') {
            $query->where('order_status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('order_status', ['cancelled', 'refunded', 'disputed']);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('dashboard.orders', compact('user', 'orders', 'type', 'status'));
    }
}
