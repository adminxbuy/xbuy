@extends('layouts.admin')

@section('title', 'Platform Settings')
@section('page_title', 'Configuration Panel')

@section('content')
<div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden p-6" x-data="{ 
    tab: 'general', 
    testEmail: '', 
    testSuccess: '', 
    testError: '', 
    testing: false, 
    libraryOpen: false,
    libraryImages: [],
    libraryTarget: '',
    currentLogo: '{{ $settings->firstWhere('key', 'website_logo')->value ?? '/website_assets/images/logo.png' }}',
    currentFavicon: '{{ $settings->firstWhere('key', 'website_favicon')->value ?? '/website_assets/images/favicon.ico' }}',
    openLibrary: async function(target) {
        this.libraryTarget = target;
        this.libraryOpen = true;
        try {
            let res = await fetch('{{ route('admin.asset-library.images') }}');
            this.libraryImages = await res.json();
            setTimeout(() => { if (window.lucide) { window.lucide.createIcons(); } }, 100);
        } catch (e) {
            console.error('Failed to load asset library', e);
        }
    },
    selectLibraryImage: function(url) {
        if (this.libraryTarget === 'logo') {
            this.currentLogo = url;
            document.getElementById('selected_logo_path').value = url;
        } else if (this.libraryTarget === 'favicon') {
            this.currentFavicon = url;
            document.getElementById('selected_favicon_path').value = url;
        }
        this.libraryOpen = false;
    },
    removeImage: function(target) {
        if (target === 'logo') {
            this.currentLogo = '';
            document.getElementById('selected_logo_path').value = 'remove';
        } else if (target === 'favicon') {
            this.currentFavicon = '';
            document.getElementById('selected_favicon_path').value = 'remove';
        }
    },
    testSMTP: async function() {
        if (!this.testEmail) {
            this.testError = 'Please enter a test email address.';
            return;
        }
        this.testing = true;
        this.testSuccess = '';
        this.testError = '';
        try {
            let response = await fetch('{{ route('admin.settings.smtp.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    smtp_host: document.getElementById('smtp_host').value,
                    smtp_port: document.getElementById('smtp_port').value,
                    smtp_username: document.getElementById('smtp_username').value,
                    smtp_password: document.getElementById('smtp_password').value,
                    smtp_from_email: document.getElementById('smtp_from_email').value,
                    smtp_from_name: document.getElementById('smtp_from_name').value,
                    smtp_encryption: document.getElementById('smtp_encryption').value,
                    test_email: this.testEmail
                })
            });
            let result = await response.json();
            if (result.success) {
                this.testSuccess = result.message;
            } else {
                this.testError = result.message;
            }
        } catch(err) {
            this.testError = 'SMTP test request failed: ' + err.message;
        } finally {
            this.testing = false;
        }
    } 
}">
    <!-- Tabs Header -->
    <div class="flex border-b border-zinc-200 mb-6 overflow-x-auto pb-1 gap-1">
        @foreach([
            ['id' => 'general', 'label' => 'General', 'icon' => 'settings'],
            ['id' => 'listings', 'label' => 'Listings', 'icon' => 'package'],
            ['id' => 'orders', 'label' => 'Orders', 'icon' => 'shopping-cart'],
            ['id' => 'seller', 'label' => 'Seller', 'icon' => 'users'],
            ['id' => 'buyer', 'label' => 'Buyer', 'icon' => 'user'],
            ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'alert-triangle'],
            ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
            ['id' => 'seo', 'label' => 'SEO', 'icon' => 'globe'],
            ['id' => 'social', 'label' => 'Social', 'icon' => 'share-2'],
            ['id' => 'payments', 'label' => 'Payments', 'icon' => 'credit-card'],
            ['id' => 'tickets', 'label' => 'Tickets', 'icon' => 'life-buoy'],
            ['id' => 'smtp', 'label' => 'SMTP', 'icon' => 'mail']
        ] as $tInfo)
            <button type="button" @click="tab = '{{ $tInfo['id'] }}'" 
                    :class="tab === '{{ $tInfo['id'] }}' ? 'border-[#09090b] font-semibold text-black' : 'border-transparent text-zinc-500 hover:text-zinc-800'" 
                    class="px-4 py-2.5 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-1.5 whitespace-nowrap">
                <i data-lucide="{{ $tInfo['icon'] }}" class="w-3.5 h-3.5"></i>
                <span>{{ $tInfo['label'] }}</span>
            </button>
        @endforeach
    </div>

    <!-- Helper Blade macro for rendering setting fields -->
    @php
        $renderInput = function($setting, $type = 'text', $placeholder = '') {
            $value = $setting->value ?? '';
            $id = $setting->key;
            
            if ($setting->type === 'boolean') {
                $isChecked = filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value == '1';
                return '
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="'.$id.'" value="1" class="sr-only peer" '.($isChecked ? 'checked' : '').'>
                        <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span class="ml-3 text-xs font-semibold text-zinc-700">Enabled</span>
                    </label>
                ';
            }
            
            $inputClass = "w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white";
            
            if ($setting->type === 'integer') {
                return '<input type="number" id="'.$id.'" name="'.$id.'" value="'.$value.'" required class="'.$inputClass.'">';
            }
            
            return '<input type="text" id="'.$id.'" name="'.$id.'" value="'.$value.'" placeholder="'.$placeholder.'" class="'.$inputClass.'">';
        };
    @endphp

    <!-- 1. General Tab -->
    <div x-show="tab === 'general'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="general">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="settings" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                General Policy & Rates
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['platform_name', 'platform_tagline', 'support_email', 'contact_phone', 'maintenance_message', 'auto_release_escrow_days', 'platform_active', 'currency_selector', 'timezone_selector'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            @if($key === 'currency_selector')
                                <select name="currency_selector" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    <option value="INR" {{ $setting->value === 'INR' ? 'selected' : '' }}>INR (Indian Rupee - ₹)</option>
                                    <option value="USD" {{ $setting->value === 'USD' ? 'selected' : '' }}>USD (US Dollar - $)</option>
                                </select>
                            @elseif($key === 'timezone_selector')
                                <select name="timezone_selector" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    <option value="Asia/Kolkata" {{ $setting->value === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (Indian Standard Time - IST)</option>
                                    <option value="UTC" {{ $setting->value === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York" {{ $setting->value === 'America/New_York' ? 'selected' : '' }}>America/New_York (Eastern Time - EST/EDT)</option>
                                    <option value="Europe/London" {{ $setting->value === 'Europe/London' ? 'selected' : '' }}>Europe/London (Greenwich Mean Time - GMT/BST)</option>
                                </select>
                            @else
                                {!! $renderInput($setting) !!}
                            @endif
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach

                <!-- Logo Upload -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Website Logo</label>
                    <input type="hidden" name="selected_logo_path" id="selected_logo_path" value="">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-xl border border-zinc-200 flex items-center justify-center bg-zinc-50 overflow-hidden flex-shrink-0">
                            <template x-if="currentLogo">
                                <img :src="currentLogo" class="object-contain max-w-full max-h-full p-1">
                            </template>
                            <template x-if="!currentLogo">
                                <div class="text-zinc-400 text-[10px] uppercase font-bold">No Logo</div>
                            </template>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="openLibrary('logo')" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-semibold border border-zinc-200 transition-all flex items-center space-x-1">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                    <span>Choose from Library</span>
                                </button>
                                <label class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center space-x-1">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload File</span>
                                    <input type="file" name="website_logo" accept="image/*" class="hidden" @change="currentLogo = URL.createObjectURL($event.target.files[0]); document.getElementById('selected_logo_path').value = ''">
                                </label>
                                <template x-if="currentLogo">
                                    <button type="button" @click="removeImage('logo')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 rounded-lg text-xs font-semibold transition-all flex items-center space-x-1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Remove</span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-[10px] text-zinc-400">Recommended: 250x80px PNG or SVG.</p>
                        </div>
                    </div>
                </div>

                <!-- Favicon Upload -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Website Favicon</label>
                    <input type="hidden" name="selected_favicon_path" id="selected_favicon_path" value="">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-xl border border-zinc-200 flex items-center justify-center bg-zinc-50 overflow-hidden flex-shrink-0">
                            <template x-if="currentFavicon">
                                <img :src="currentFavicon" class="object-contain w-6 h-6">
                            </template>
                            <template x-if="!currentFavicon">
                                <div class="text-zinc-400 text-[10px] uppercase font-bold">No Icon</div>
                            </template>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="openLibrary('favicon')" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-semibold border border-zinc-200 transition-all flex items-center space-x-1">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                    <span>Choose from Library</span>
                                </button>
                                <label class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center space-x-1">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Upload File</span>
                                    <input type="file" name="website_favicon" accept="image/x-icon,image/png,image/gif" class="hidden" @change="currentFavicon = URL.createObjectURL($event.target.files[0]); document.getElementById('selected_favicon_path').value = ''">
                                </label>
                                <template x-if="currentFavicon">
                                    <button type="button" @click="removeImage('favicon')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 rounded-lg text-xs font-semibold transition-all flex items-center space-x-1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Remove</span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-[10px] text-zinc-400">Recommended: 32x32px ICO or PNG.</p>
                        </div>
                    </div>
                </div>

                <!-- Promo banner options removed. Fallback code-based banner active on frontend. -->
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save General Settings
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Listings Tab -->
    <div x-show="tab === 'listings'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="listings">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="package" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Listings Configuration
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['max_images_per_listing', 'min_images_per_listing', 'max_listing_price', 'min_listing_price', 'listing_expiry_days', 'auto_approve_fulfilled_sellers', 'require_serial_number', 'require_grade'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Listings Config
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Orders Tab -->
    <div x-show="tab === 'orders'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="orders">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="shopping-cart" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Orders & Shipping Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['order_id_prefix', 'max_order_value', 'shipping_charge_default', 'free_shipping_above', 'cash_on_delivery'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Orders Settings
                </button>
            </div>
        </form>
    </div>

    <!-- 4. Seller Tab -->
    <div x-show="tab === 'seller'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="seller">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="users" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Seller Portal Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['kyc_required_before_listing', 'min_images_required', 'auto_badge_calculation', 'seller_response_deadline_hours', 'fulfilled_min_orders', 'fulfilled_min_rating', 'fulfilled_min_cooperation_score', 'fulfilled_response_time_hours'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Seller Config
                </button>
            </div>
        </form>
    </div>

    <!-- 5. Buyer Tab -->
    <div x-show="tab === 'buyer'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="buyer">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="user" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Buyer System Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['trusted_buyer_min_orders', 'verified_buyer_min_orders', 'buyer_badge_visible_to_seller', 'buyer_badge_visible_to_buyer'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Buyer Config
                </button>
            </div>
        </form>
    </div>

    <!-- 6. Disputes Tab -->
    <div x-show="tab === 'disputes'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="dispute">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="alert-triangle" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Disputes Policy Configuration
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['dispute_window_after_delivery_days', 'escalation_after_hours', 'auto_escalate', 'dispute_evidence_max_images', 'dispute_evidence_max_size_mb'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Disputes Config
                </button>
            </div>
        </form>
    </div>

    <!-- 7. Notifications Tab -->
    <div x-show="tab === 'notifications'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="notifications">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="bell" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Notifications & Email Dispatch
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['send_order_email', 'send_dispute_email', 'send_badge_email', 'send_payment_email', 'send_weekly_seller_summary', 'admin_email_on_new_dispute', 'admin_email_on_new_seller', 'admin_email_on_high_dispute_rate'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Notifications Config
                </button>
            </div>
        </form>
    </div>

    <!-- 8. SEO Tab -->
    <div x-show="tab === 'seo'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="seo">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="globe" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                SEO Metadata & Indexing
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['meta_title_suffix', 'meta_description_default', 'og_image_default', 'google_analytics_id', 'google_search_console_key'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save SEO Config
                </button>
            </div>
        </form>
    </div>

    <!-- 9. Social Tab -->
    <div x-show="tab === 'social'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="social">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="share-2" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Social Media Links
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['facebook_url', 'instagram_url', 'twitter_url', 'youtube_url', 'whatsapp_number'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- GET THE APP Section -->
            <div class="mt-2 pt-6 border-t border-zinc-200">
                <h3 class="text-base font-semibold text-zinc-900 pb-2 mb-4 flex items-center">
                    <i data-lucide="smartphone" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                    Get the App
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    @if($setting = $settings->firstWhere('key', 'playstore_url'))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.18 23.76c.3.17.65.19.97.07l12.4-7.16-2.67-2.67-10.7 9.76zm-1.18-21.5v19.48l10.81-9.74L2 2.26zm19.34 8.74L18.9 8.76 16.06 11.6 18.9 14.43l2.46-1.43c.7-.41.7-1.5 0-1.9zM4.15.17L16.55 7.33l-2.67 2.67L3.18.24C2.86.1 2.5.13 2.22.31L4.15.17z"/></svg>
                                Play Store URL
                            </label>
                            {!! $renderInput($setting, 'text', 'https://play.google.com/store/apps/details?id=com.yourapp') !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                    <div class="flex items-start pt-5">
                        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 text-xs text-zinc-500 space-y-1.5 w-full">
                            <p class="font-semibold text-zinc-700 flex items-center gap-1.5">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-blue-400"></i>
                                How to get your Play Store link
                            </p>
                            <ol class="list-decimal ml-4 space-y-1 leading-relaxed">
                                <li>Open <strong>Google Play Console</strong></li>
                                <li>Go to your app → <strong>Store listing</strong></li>
                                <li>Copy the link: <span class="font-mono bg-zinc-200 px-1 rounded">play.google.com/store/apps/details?id=...</span></li>
                                <li>Paste the full URL above and save</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Authentication Setup -->
            <div class="mt-2 pt-6 border-t border-zinc-200">
                <h3 class="text-base font-semibold text-zinc-900 pb-2 mb-4 flex items-center">
                    <i data-lucide="shield-check" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                    Social Sign-in Configuration
                </h3>
                
                <!-- Google Sign-in -->
                <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-xl mb-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-200/60 pb-2">
                        <span class="font-bold text-xs uppercase tracking-wider text-zinc-800 flex items-center gap-1.5">
                            <i data-lucide="chrome" class="w-4 h-4 text-[#4285F4]"></i> Google OAuth Settings
                        </span>
                        @if($setting = $settings->firstWhere('key', 'google_login_enabled'))
                            {!! $renderInput($setting) !!}
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['google_client_id', 'google_client_secret'] as $key)
                            @if($setting = $settings->firstWhere('key', $key))
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('google_', '', str_replace('_', ' ', $setting->key)) }}</label>
                                    @if(str_contains($key, 'secret'))
                                        <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    @else
                                        {!! $renderInput($setting) !!}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                        <div class="col-span-2">
                            <p class="text-[10px] text-zinc-400 font-mono">Callback URL: <span class="bg-zinc-200 px-1.5 py-0.5 rounded text-zinc-750 select-all">{{ url('/auth/social/google/callback') }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Facebook Sign-in -->
                <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-xl mb-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-200/60 pb-2">
                        <span class="font-bold text-xs uppercase tracking-wider text-zinc-800 flex items-center gap-1.5">
                            <i data-lucide="facebook" class="w-4 h-4 text-[#1877f2]"></i> Facebook OAuth Settings
                        </span>
                        @if($setting = $settings->firstWhere('key', 'facebook_login_enabled'))
                            {!! $renderInput($setting) !!}
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['facebook_client_id', 'facebook_client_secret'] as $key)
                            @if($setting = $settings->firstWhere('key', $key))
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('facebook_', '', str_replace('_', ' ', $setting->key)) }}</label>
                                    @if(str_contains($key, 'secret'))
                                        <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    @else
                                        {!! $renderInput($setting) !!}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                        <div class="col-span-2">
                            <p class="text-[10px] text-zinc-400 font-mono">Callback URL: <span class="bg-zinc-200 px-1.5 py-0.5 rounded text-zinc-750 select-all">{{ url('/auth/social/facebook/callback') }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Apple Sign-in -->
                <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-xl mb-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-200/60 pb-2">
                        <span class="font-bold text-xs uppercase tracking-wider text-zinc-800 flex items-center gap-1.5">
                            <i data-lucide="aperture" class="w-4 h-4 text-black"></i> Apple OAuth Settings
                        </span>
                        @if($setting = $settings->firstWhere('key', 'apple_login_enabled'))
                            {!! $renderInput($setting) !!}
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['apple_client_id', 'apple_client_secret'] as $key)
                            @if($setting = $settings->firstWhere('key', $key))
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('apple_', '', str_replace('_', ' ', $setting->key)) }}</label>
                                    @if(str_contains($key, 'secret'))
                                        <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    @else
                                        {!! $renderInput($setting) !!}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                        <div class="col-span-2">
                            <p class="text-[10px] text-zinc-400 font-mono">Callback URL: <span class="bg-zinc-200 px-1.5 py-0.5 rounded text-zinc-750 select-all">{{ url('/auth/social/apple/callback') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Policy Pages Slugs -->
            <div class="mt-2 pt-6 border-t border-zinc-200">
                <h3 class="text-base font-semibold text-zinc-900 pb-2 mb-4 flex items-center">
                    <i data-lucide="file-text" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                    Dynamic Policy Slugs
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    @foreach(['terms_page_slug', 'privacy_page_slug'] as $key)
                        @if($setting = $settings->firstWhere('key', $key))
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                                {!! $renderInput($setting) !!}
                                <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Social & App Links
                </button>
            </div>
        </form>
    </div>

    <!-- 10. Payments Tab (Razorpay & Shiprocket) -->
    <div x-show="tab === 'payments'" x-cloak>
        <!-- Razorpay Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="razorpay">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="credit-card" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Razorpay Payment Gateway Setup
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['razorpay_mode', 'razorpay_key_id', 'razorpay_key_secret', 'razorpay_webhook_secret', 'razorpay_route_enabled', 'razorpay_escrow_hold_days'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            @if($key === 'razorpay_mode')
                                <select name="razorpay_mode" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                    <option value="test" {{ $setting->value === 'test' ? 'selected' : '' }}>test (Sandbox mode)</option>
                                    <option value="live" {{ $setting->value === 'live' ? 'selected' : '' }}>live (Production mode)</option>
                                </select>
                            @else
                                {!! $renderInput($setting) !!}
                            @endif
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Razorpay Credentials
                </button>
            </div>
        </form>

        <!-- Shiprocket Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 mt-10">
            @csrf
            <input type="hidden" name="settings_group" value="shiprocket">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="truck" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Shiprocket Delivery Settings
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['shiprocket_active', 'shiprocket_email', 'shiprocket_password', 'shiprocket_channel_id', 'default_courier'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            @if($key === 'shiprocket_password')
                                <input type="password" name="shiprocket_password" value="{{ $setting->value }}" class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                            @else
                                {!! $renderInput($setting) !!}
                            @endif
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Shiprocket Settings
                </button>
            </div>
        </form>
    </div>

    <!-- 11. SMTP Tab -->
    <div x-show="tab === 'smtp'" x-cloak class="space-y-6">
        <form action="{{ route('admin.settings.smtp.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="mail" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                SMTP Server Config
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @forelse($settings->where('group', 'smtp') as $setting)
                    <div class="space-y-1.5">
                        <label for="{{ $setting->key }}" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">
                            {{ ucwords(str_replace('smtp ', '', str_replace('_', ' ', $setting->key))) }}
                        </label>
                        
                        @if($setting->key === 'smtp_encryption')
                            <select id="{{ $setting->key }}" name="{{ $setting->key }}" 
                                    class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                                <option value="TLS" {{ $setting->value === 'TLS' ? 'selected' : '' }}>TLS</option>
                                <option value="SSL" {{ $setting->value === 'SSL' ? 'selected' : '' }}>SSL</option>
                                <option value="None" {{ $setting->value === 'None' ? 'selected' : '' }}>None</option>
                            </select>
                        @elseif($setting->key === 'smtp_password')
                            <input type="password" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                   class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                        @else
                            <input type="text" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                   class="w-full p-3 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                        @endif

                        @if($setting->description)
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-zinc-450 col-span-2 py-8 text-center">SMTP parameters are missing. Please re-seed.</p>
                @endforelse
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save SMTP Config
                </button>
            </div>
        </form>

        <!-- SMTP Connection Test block -->
        <div class="p-5 bg-zinc-50 border border-zinc-200 rounded-xl space-y-4">
            <h4 class="font-bold text-zinc-850 text-xs uppercase tracking-wider flex items-center">
                <i data-lucide="send" class="w-4 h-4 mr-2 text-zinc-600"></i>
                Test SMTP Mail Server Connection
            </h4>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="email" x-model="testEmail" placeholder="Enter recipient email address..."
                       class="flex-1 px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white">
                <button type="button" @click="testSMTP()" :disabled="testing"
                        class="bg-black hover:bg-zinc-800 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-sm transition-all disabled:opacity-50 flex items-center justify-center space-x-2">
                    <span x-show="!testing">Test Connection</span>
                    <span x-show="testing" class="flex items-center space-x-2">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Testing...</span>
                    </span>
                </button>
            </div>

            <div x-show="testSuccess" x-cloak class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold" x-text="testSuccess"></div>
            <div x-show="testError" x-cloak class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold" x-text="testError"></div>
        </div>
    </div>

    <!-- 12. Tickets Tab -->
    <div x-show="tab === 'tickets'" x-cloak>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="tickets">
            <h3 class="text-base font-semibold text-zinc-900 border-b border-zinc-150 pb-2 flex items-center">
                <i data-lucide="life-buoy" class="w-4.5 h-4.5 mr-2 text-zinc-550"></i>
                Support Tickets Configuration
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                @foreach(['ticket_admin_email', 'ticket_email_notifications_enabled', 'ticket_whatsapp_notifications_enabled', 'ticket_whatsapp_number'] as $key)
                    @if($setting = $settings->firstWhere('key', $key))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ str_replace('_', ' ', $setting->key) }}</label>
                            {!! $renderInput($setting) !!}
                            <p class="text-[10px] text-zinc-400 leading-relaxed">{{ $setting->description }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="pt-5 border-t border-zinc-150 flex justify-end">
                <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all text-xs active:scale-[0.98]">
                    Save Tickets Config
                </button>
            </div>
        </form>
    </div>

    <!-- Asset Library Modal -->
    <div x-show="libraryOpen" x-transition x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-zinc-200" @click.away="libraryOpen = false">
            <!-- Modal Header -->
            <div class="p-4 border-b border-zinc-150 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i data-lucide="image" class="w-5 h-5 text-zinc-550"></i>
                    <h3 class="text-sm font-bold text-zinc-800 uppercase tracking-wider">Select from Asset Library</h3>
                </div>
                <button type="button" @click="libraryOpen = false" class="text-zinc-400 hover:text-zinc-600 focus:outline-none">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1 bg-zinc-50">
                <template x-if="libraryImages.length === 0">
                    <div class="text-center py-12 text-zinc-450">
                        <i data-lucide="image-off" class="w-10 h-10 mx-auto mb-3 text-zinc-300"></i>
                        <p class="text-xs font-semibold">No assets found in library</p>
                        <p class="text-[10px] text-zinc-400 mt-1">Upload images to public/website_assets/images directory</p>
                    </div>
                </template>
                <template x-if="libraryImages.length > 0">
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                        <template x-for="img in libraryImages" :key="img.url">
                            <button type="button" @click="selectLibraryImage(img.url)" 
                                    class="group p-2 bg-white rounded-xl border border-zinc-200 hover:border-[#09090b] hover:ring-2 hover:ring-[#09090b]/20 transition-all text-left flex flex-col items-center justify-between aspect-square">
                                <div class="w-full flex-1 flex items-center justify-center overflow-hidden rounded-lg bg-zinc-50">
                                    <img :src="img.url" class="max-h-24 max-w-full object-contain p-1 group-hover:scale-105 transition-all">
                                </div>
                                <div class="w-full mt-2 text-center">
                                    <p class="text-[9px] font-semibold text-zinc-700 truncate" x-text="img.name"></p>
                                    <p class="text-[8px] text-zinc-400" x-text="img.size"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
            <!-- Modal Footer -->
            <div class="p-4 border-t border-zinc-150 flex justify-end bg-zinc-50 rounded-b-2xl">
                <button type="button" @click="libraryOpen = false" class="px-4 py-2 border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 rounded-xl text-xs font-semibold shadow-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
