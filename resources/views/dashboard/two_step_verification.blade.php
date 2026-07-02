@extends('layouts.app')

@section('title', 'Verify Phone Number')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            @include('dashboard.partials._sidebar', ['active' => 'security'])

            <!-- Right Content Area (Centered Content Form) -->
            <div class="flex-1 max-w-lg mx-auto md:mx-0 py-6 text-center md:text-left space-y-8">
                
                <!-- Alert Banner for errors/success messages -->
                <div id="alert-banner" class="hidden p-4 rounded-sm text-sm font-semibold border"></div>

                @if(session('success'))
                    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-sm text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Dynamic Wrapper to transition steps -->
                <div class="bg-white border border-zinc-200 rounded-sm p-8 shadow-sm transition-all duration-300">
                    
                    <!-- STEP 1: Phone entry form -->
                    <div id="step-phone" class="space-y-6">
                        <div class="space-y-3">
                            <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Verify your phone number</h2>
                            <p class="text-sm text-zinc-500 leading-relaxed">
                                We will send a confirmation code via SMS to verify that this is your number.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <!-- Country prefix and phone input -->
                            <div class="flex items-center gap-3 border-b border-zinc-200 pb-2.5 focus-within:border-zinc-800 transition-colors duration-150 max-w-sm mx-auto md:mx-0">
                                <div class="text-base font-semibold text-zinc-800 select-none pr-1">
                                    +91
                                </div>
                                <div class="h-5 w-[1px] bg-zinc-300"></div>
                                <input type="tel" id="phone" name="phone" required 
                                       placeholder="Enter phone number" 
                                       value="{{ old('phone', $user->phone ?? '') }}"
                                       class="flex-1 w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
                            </div>

                            <!-- Invisible reCAPTCHA container for Firebase security -->
                            <div id="recaptcha-container" class="my-2"></div>

                            <!-- Send button -->
                            <button type="button" id="btn-send-otp" class="w-full md:w-auto px-12 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider transition-opacity hover:opacity-90">
                                Send OTP
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Verification Code Form (Hidden by default) -->
                    <div id="step-otp" class="hidden space-y-6">
                        <div class="space-y-3">
                            <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Enter verification code</h2>
                            <p id="otp-message" class="text-sm text-zinc-500">Please enter the 6-digit confirmation code sent to your phone.</p>
                        </div>

                        <form id="form-verify-otp" action="/dashboard/settings/security/two-step/verify" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" id="verified-phone-input" name="phone">
                            <input type="hidden" id="firebase-id-token-input" name="firebase_id_token">

                            <div class="border-b border-zinc-200 focus-within:border-zinc-800 pb-2 transition-colors duration-150">
                                <input type="text" id="otp-code" name="otp" required maxlength="6" 
                                       placeholder="Enter 6-digit code" 
                                       class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350 tracking-widest text-center md:text-left">
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 items-center">
                                <button type="submit" class="w-full sm:w-auto px-12 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider transition-opacity hover:opacity-90">
                                    Verify
                                </button>
                                <button type="button" id="btn-back" class="text-xs text-zinc-400 hover:text-zinc-600 font-semibold underline">
                                    Change Number
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Help link -->
                <div>
                    <a href="#" class="text-xs text-zinc-450 hover:text-zinc-650 transition-colors">
                        Having trouble? <span class="text-[#007f87] hover:underline font-semibold">Get help</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Firebase App & Auth SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
    // Initialize Firebase
    const firebaseConfig = {
        apiKey: "{{ env('VITE_FIREBASE_API_KEY') }}",
        authDomain: "{{ env('VITE_FIREBASE_AUTH_DOMAIN') }}",
        projectId: "{{ env('VITE_FIREBASE_PROJECT_ID') }}",
        storageBucket: "{{ env('VITE_FIREBASE_STORAGE_BUCKET') }}",
        messagingSenderId: "{{ env('VITE_FIREBASE_MESSAGING_SENDER_ID') }}",
        appId: "{{ env('VITE_FIREBASE_APP_ID') }}"
    };

    // Only attempt initialization if API key is provided
    if (firebaseConfig.apiKey) {
        firebase.initializeApp(firebaseConfig);
    } else {
        console.warn("Firebase configuration keys are missing in your environment (.env) file.");
    }

    let confirmationResult = null;
    let recaptchaVerifier = null;

    const alertBanner = document.getElementById('alert-banner');
    const stepPhone = document.getElementById('step-phone');
    const stepOtp = document.getElementById('step-otp');
    const btnSendOtp = document.getElementById('btn-send-otp');
    const otpMessage = document.getElementById('otp-message');
    const phoneInput = document.getElementById('phone');
    const otpCodeInput = document.getElementById('otp-code');
    const verifiedPhoneInput = document.getElementById('verified-phone-input');
    const firebaseIdTokenInput = document.getElementById('firebase-id-token-input');
    const formVerifyOtp = document.getElementById('form-verify-otp');
    const btnBack = document.getElementById('btn-back');

    function showAlert(message, type = 'error') {
        alertBanner.classList.remove('hidden', 'bg-rose-50', 'text-rose-800', 'border-rose-200', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
        if (type === 'error') {
            alertBanner.classList.add('bg-rose-50', 'text-rose-800', 'border-rose-200');
        } else {
            alertBanner.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
        }
        alertBanner.textContent = message;
        alertBanner.classList.remove('hidden');
    }

    function hideAlert() {
        alertBanner.classList.add('hidden');
    }

    // Initialize recaptcha
    function initRecaptcha() {
        if (!recaptchaVerifier) {
            recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    // reCAPTCHA solved
                }
            });
        }
    }

    btnSendOtp.addEventListener('click', function() {
        hideAlert();
        let rawPhone = phoneInput.value.trim();
        if (!rawPhone) {
            showAlert('Please enter a valid phone number.');
            return;
        }

        // Clean phone number (remove any spaces, dashes, etc.)
        rawPhone = rawPhone.replace(/[^0-9]/g, '');

        if (rawPhone.length < 10) {
            showAlert('Please enter a valid 10-digit mobile number.');
            return;
        }

        // Prefix +91 for India
        const formattedPhone = '+91' + rawPhone.slice(-10);

        if (!firebaseConfig.apiKey) {
            showAlert('Firebase integration is not fully configured yet. Please configure the .env settings.');
            return;
        }

        btnSendOtp.disabled = true;
        btnSendOtp.textContent = 'Sending OTP...';

        try {
            initRecaptcha();
            
            firebase.auth().signInWithPhoneNumber(formattedPhone, recaptchaVerifier)
                .then((result) => {
                    confirmationResult = result;
                    showAlert('OTP Sent Successfully!', 'success');
                    
                    // Populate inputs
                    verifiedPhoneInput.value = formattedPhone;
                    otpMessage.textContent = `Please enter the 6-digit confirmation code sent to ${formattedPhone}.`;
                    
                    // Transition views
                    stepPhone.classList.add('hidden');
                    stepOtp.classList.remove('hidden');
                })
                .catch((error) => {
                    console.error("SMS send error", error);
                    showAlert(error.message || 'Failed to send OTP. Please try again.');
                    btnSendOtp.disabled = false;
                    btnSendOtp.textContent = 'Send OTP';
                    if (recaptchaVerifier) {
                        recaptchaVerifier.clear();
                        recaptchaVerifier = null;
                    }
                });
        } catch (err) {
            showAlert('Error initializing verification system.');
            btnSendOtp.disabled = false;
            btnSendOtp.textContent = 'Send OTP';
        }
    });

    btnBack.addEventListener('click', function() {
        stepOtp.classList.add('hidden');
        stepPhone.classList.remove('hidden');
        btnSendOtp.disabled = false;
        btnSendOtp.textContent = 'Send OTP';
        hideAlert();
        if (recaptchaVerifier) {
            recaptchaVerifier.clear();
            recaptchaVerifier = null;
        }
    });

    formVerifyOtp.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();

        const otp = otpCodeInput.value.trim();
        if (otp.length !== 6) {
            showAlert('Please enter a valid 6-digit OTP code.');
            return;
        }

        if (!confirmationResult) {
            showAlert('Session expired. Please request a new OTP.');
            return;
        }

        const submitBtn = formVerifyOtp.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Verifying...';

        confirmationResult.confirm(otp)
            .then((result) => {
                // Get User ID Token to pass to server
                return result.user.getIdToken();
            })
            .then((idToken) => {
                firebaseIdTokenInput.value = idToken;
                showAlert('OTP verified! Finalizing configuration...', 'success');
                // Submit form to Laravel backend to save settings
                formVerifyOtp.submit();
            })
            .catch((error) => {
                console.error("OTP Verification Error", error);
                showAlert('The verification code is incorrect or expired.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Verify';
            });
    });
</script>
@endsection

