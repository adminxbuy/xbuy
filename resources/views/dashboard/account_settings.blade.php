@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            @include('dashboard.partials._sidebar', ['active' => 'account'])

            <!-- Right Content Area -->
            <div class="flex-1 space-y-8" x-data="{ 
                    vacationMode: {{ $user->vacation_mode ? 'true' : 'false' }}, 
                    editingEmail: false, 
                    emailVal: '{{ old('email', $user->email) }}',
                    editingPhone: false,
                    phoneVal: '{{ old('phone', $user->phone) }}',
                    isPhoneVerified: {{ $user->is_phone_verified ? 'true' : 'false' }},
                    showOtpModal: false,
                    showPasswordModal: false,
                    showDeleteModal: false,
                    otpCode: '',
                    otpError: '',
                    otpSuccess: '',
                    confirmationResult: null,
                    recaptchaVerifier: null,
                    isSendingOtp: false,
                    isVerifyingOtp: false,
                    init() {
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('change_password') === '1') {
                            this.showPasswordModal = true;
                        }
                    },
                    initRecaptcha() {
                        if (!this.recaptchaVerifier) {
                            this.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                                'size': 'invisible'
                            });
                        }
                    },
                    async sendFirebaseOtp() {
                        this.otpError = '';
                        this.otpSuccess = '';
                        let rawPhone = this.phoneVal.trim();
                        if (!rawPhone) {
                            this.otpError = 'Please enter a valid phone number.';
                            return;
                        }
                        // Clean
                        rawPhone = rawPhone.replace(/[^0-9]/g, '');
                        if (rawPhone.length < 10) {
                            this.otpError = 'Please enter a valid 10-digit mobile number.';
                            return;
                        }
                        const formattedPhone = '+91' + rawPhone.slice(-10);

                        this.isSendingOtp = true;
                        try {
                            this.initRecaptcha();
                            const result = await firebase.auth().signInWithPhoneNumber(formattedPhone, this.recaptchaVerifier);
                            this.confirmationResult = result;
                            this.showOtpModal = true;
                            this.otpSuccess = 'OTP Sent successfully!';
                            this.isSendingOtp = false;
                        } catch (error) {
                            console.error(error);
                            this.otpError = error.message || 'Failed to send OTP.';
                            this.isSendingOtp = false;
                            if (this.recaptchaVerifier) {
                                this.recaptchaVerifier.clear();
                                this.recaptchaVerifier = null;
                            }
                        }
                    },
                    async verifyFirebaseOtp() {
                        this.otpError = '';
                        this.otpSuccess = '';
                        if (this.otpCode.trim().length !== 6) {
                            this.otpError = 'Please enter a valid 6-digit code.';
                            return;
                        }
                        if (!this.confirmationResult) {
                            this.otpError = 'Session expired. Please request a new OTP.';
                            return;
                        }

                        this.isVerifyingOtp = true;
                        try {
                            const result = await this.confirmationResult.confirm(this.otpCode.trim());
                            const idToken = await result.user.getIdToken();
                            
                            // Call backend to update DB
                            const response = await fetch('/dashboard/settings/verify-phone', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ 
                                    phone: '+91' + this.phoneVal.trim().replace(/[^0-9]/g, '').slice(-10),
                                    firebase_id_token: idToken 
                                })
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                this.isPhoneVerified = true;
                                this.otpSuccess = 'Phone verified successfully!';
                                setTimeout(() => { 
                                    this.showOtpModal = false; 
                                    this.otpSuccess = ''; 
                                    this.otpCode = ''; 
                                    window.location.reload();
                                }, 1500);
                            } else {
                                this.otpError = data.message || 'Verification failed on server.';
                            }
                        } catch (error) {
                            console.error(error);
                            this.otpError = 'Invalid OTP code. Please try again.';
                        } finally {
                            this.isVerifyingOtp = false;
                        }
                    }
                }">
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/dashboard/settings/account" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Email and Phone Verification Card -->
                    <div class="space-y-2">
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            <!-- Email Row -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div class="space-y-1 flex-1">
                                    <span class="text-xs text-zinc-400 font-semibold block">Email</span>
                                    <template x-if="!editingEmail">
                                        <div>
                                            <span class="text-sm font-bold text-zinc-800" x-text="emailVal"></span>
                                            <input type="hidden" name="email" :value="emailVal">
                                            <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium mt-0.5">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Verified</span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="editingEmail">
                                        <div class="flex items-center gap-2">
                                            <input type="email" name="email" x-model="emailVal" class="text-sm text-zinc-800 border border-zinc-300 rounded-sm px-3 py-1.5 focus:border-[#e6c019] focus:outline-none w-64">
                                            <button type="button" @click="editingEmail = false" class="px-3 py-1.5 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
                                                Done
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="!editingEmail">
                                    <button type="button" @click="editingEmail = true" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors">
                                        Change
                                    </button>
                                </div>
                            </div>

                            <!-- Phone Row -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div class="space-y-1 flex-1">
                                    <span class="text-xs text-zinc-400 font-semibold block">Phone number</span>
                                    <template x-if="!editingPhone">
                                        <div>
                                            <span class="text-sm font-bold text-zinc-800" x-text="phoneVal || 'Not configured'"></span>
                                            <input type="hidden" name="phone" :value="phoneVal">
                                            
                                            <!-- Verified Badge -->
                                            <div x-show="isPhoneVerified && phoneVal" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium mt-0.5">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Verified</span>
                                            </div>

                                            <!-- Unverified Badge -->
                                            <div x-show="!isPhoneVerified && phoneVal" class="flex items-center gap-1.5 text-xs text-amber-600 font-medium mt-0.5">
                                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                                <span>Unverified</span>
                                            </div>

                                            <!-- Dynamic Error Badge (no popups) -->
                                            <div x-show="otpError" class="flex items-center gap-1.5 text-xs text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-sm mt-2 w-fit font-medium">
                                                <span x-text="otpError"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="editingPhone">
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="phone" x-model="phoneVal" class="text-sm text-zinc-800 border border-zinc-300 rounded-sm px-3 py-1.5 focus:border-[#e6c019] focus:outline-none w-64">
                                            <button type="button" @click="editingPhone = false; isPhoneVerified = false" class="px-3 py-1.5 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
                                                Done
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2" x-show="!editingPhone">
                                    <button type="button" x-show="phoneVal && !isPhoneVerified" @click="sendFirebaseOtp()" :disabled="isSendingOtp" class="px-4 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
                                        <span x-text="isSendingOtp ? 'Sending...' : 'Verify'"></span>
                                    </button>
                                    <button type="button" @click="editingPhone = true" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors">
                                        Change
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-zinc-400 px-1 leading-relaxed">
                            Your phone number will only be used to help you log in. It won't be made public or used for marketing purposes.
                        </p>
                    </div>

                    <!-- Invisible Recaptcha Container -->
                    <div id="recaptcha-container" class="my-2"></div>

                    <!-- OTP Verification Modal -->
                    <div x-show="showOtpModal" class="fixed inset-0 bg-black/55 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
                        <div class="bg-white rounded-md max-w-sm w-full p-6 space-y-4 shadow-xl border border-zinc-200">
                            <div class="text-center space-y-2">
                                <h3 class="text-lg font-bold text-zinc-900">Phone Verification</h3>
                                <p class="text-xs text-zinc-500">We have sent a verification code to <span class="font-bold text-zinc-700" x-text="phoneVal"></span>. Enter the 6-digit confirmation code below.</p>
                            </div>

                            <div class="space-y-1">
                                <input type="text" x-model="otpCode" placeholder="Enter code" maxlength="6" class="w-full border border-zinc-300 rounded-sm px-3 py-2 text-center text-lg font-bold tracking-widest focus:border-[#e6c019] focus:ring-1 focus:ring-[#e6c019] outline-none">
                                <template x-if="otpError">
                                    <p class="text-xs text-rose-600 text-center font-medium" x-text="otpError"></p>
                                </template>
                                <template x-if="otpSuccess">
                                    <p class="text-xs text-emerald-600 text-center font-medium" x-text="otpSuccess"></p>
                                </template>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showOtpModal = false; otpError = ''; otpCode = '';" class="flex-1 px-4 py-2 border border-zinc-200 text-zinc-700 text-xs font-bold rounded-sm hover:bg-zinc-50 transition-colors">
                                    Cancel
                                </button>
                                <button type="button" @click="verifyFirebaseOtp" :disabled="isVerifyingOtp" class="flex-1 px-4 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm hover:bg-[#fdd835] transition-colors">
                                    <span x-text="isVerifyingOtp ? 'Verifying...' : 'Verify Code'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Card -->
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                        <!-- Full Name -->
                        <div class="flex items-center p-5 gap-4">
                            <label for="full_name" class="w-32 shrink-0 font-bold text-[15px] text-zinc-800">Full name</label>
                            <div class="flex-1 max-w-md">
                                <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" placeholder="Full name" class="text-sm text-zinc-700 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none placeholder-zinc-350 pr-1 focus:ring-0 font-medium pb-0.5 w-full">
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="flex items-center p-5 gap-4">
                            <label for="gender" class="w-32 shrink-0 font-bold text-[15px] text-zinc-800">Gender</label>
                            <div class="flex items-center gap-1 border-b border-zinc-150 focus-within:border-[#e6c019]">
                                <select id="gender" name="gender" class="text-sm text-zinc-700 bg-transparent border-none outline-none cursor-pointer pr-8 focus:ring-0 font-medium pb-0.5 appearance-none">
                                    <option value="" {{ old('gender', $user->gender) === '' ? 'selected' : '' }}>Select gender</option>
                                    <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 mb-0.5 pointer-events-none -ml-5"></i>
                            </div>
                        </div>

                        <!-- Birthday -->
                        <div class="flex items-center p-5 gap-4">
                            <label for="birthday" class="w-32 shrink-0 font-bold text-[15px] text-zinc-800">Birthday</label>
                            <div class="flex items-center gap-1 border-b border-zinc-200 focus-within:border-[#e6c019]">
                                <input type="date" id="birthday" name="birthday" value="{{ old('birthday', $user->birthday) }}" class="text-sm text-zinc-700 bg-transparent border-none outline-none focus:ring-0 font-medium pb-0.5 w-40 cursor-pointer">
                                <i data-lucide="calendar" class="w-4 h-4 text-zinc-400 mb-0.5 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Vacation Mode Card -->
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden p-5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-[15px] text-zinc-800">Vacation mode</span>
                            <input type="hidden" name="vacation_mode" :value="vacationMode ? 1 : 0">
                            <button type="button" 
                                    @click="vacationMode = !vacationMode" 
                                    class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
                                    :class="vacationMode ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
                                     :class="vacationMode ? 'translate-x-6' : 'translate-x-0'"></div>
                            </button>
                        </div>
                    </div>

                    <!-- Social Accounts Links Card -->
                    <div class="space-y-2">
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            <!-- Facebook -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div>
                                    <span class="font-bold text-[15px] text-zinc-800">Facebook</span>
                                </div>
                                <div>
                                    @if($user->facebook_linked)
                                        <div class="flex items-center gap-3">
                                            <span claxss="px-4 py-2 border border-zinc-200 text-zinc-400 text-xs font-bold rounded-sm bg-zinc-50 select-none">
                                                Linked
                                            </span>
                                            <form action="{{ route('dashboard.settings.account.social.toggle', 'facebook') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold transition-colors cursor-pointer">
                                                    Unlink
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('social.redirect', 'facebook') }}" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors inline-block">
                                            Link
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Google -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div>
                                    <span class="font-bold text-[15px] text-zinc-800">Google</span>
                                </div>
                                <div>
                                    @if($user->google_linked)
                                        <div class="flex items-center gap-3">
                                            <span class="px-4 py-2 border border-zinc-200 text-zinc-400 text-xs font-bold rounded-sm bg-zinc-50 select-none">
                                                Linked
                                            </span>
                                            <form action="{{ route('dashboard.settings.account.social.toggle', 'google') }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold transition-colors cursor-pointer">
                                                    Unlink
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('social.redirect', 'google') }}" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors inline-block">
                                            Link
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-zinc-400 px-1 leading-relaxed">
                            Link to your other accounts to become a trusted, verified member.
                        </p>
                    </div>

                    <!-- Change Password Card -->
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden p-5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-[15px] text-zinc-800">Change password</span>
                            <a href="/dashboard/settings/change-password" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors block">
                                Change
                            </a>
                        </div>
                    </div>

                    <!-- Delete Account Card -->
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden p-5">
                        <a href="/dashboard/settings/delete-account" class="flex items-center justify-between gap-4 text-left group">
                            <span class="font-bold text-[15px] text-zinc-800">Delete my account</span>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400 transition-transform duration-200"></i>
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-8 py-3 bg-[#e6c019] text-black font-bold text-sm rounded-sm cursor-pointer">
                            Save
                        </button>
                    </div>
                </form>



            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e4e4e7;
        font-family: 'Satoshi', sans-serif;
        padding: 4px;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #e6c019 !important;
        border-color: #e6c019 !important;
        color: #000000 !important;
        font-weight: bold;
    }
    .flatpickr-day.today {
        border-color: #e6c019 !important;
    }
    .flatpickr-day.today:hover {
        background: #e6c019 !important;
        color: #000000 !important;
    }
    .flatpickr-day:hover {
        background: #f4f4f5 !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg {
        fill: #e6c019 !important;
    }
    .flatpickr-current-month .numInputWrapper span.arrowUp:after {
        border-bottom-color: #e6c019 !important;
    }
    .flatpickr-current-month .numInputWrapper span.arrowDown:after {
        border-top-color: #e6c019 !important;
    }
</style>

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
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#birthday", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true,
            maxDate: new Date(),
            onReady: function(selectedDates, dateStr, instance) {
                // Style altInput to match existing theme
                if (instance.altInput) {
                    instance.altInput.className = "text-sm text-zinc-700 bg-transparent border-none outline-none focus:ring-0 font-medium pb-0.5 w-40 cursor-pointer";
                    instance.altInput.placeholder = "Select birthday";
                }
            }
        });
    });
</script>
@endsection
