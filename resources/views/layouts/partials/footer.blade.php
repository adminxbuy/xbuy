<!-- Mercari-Standard Global Master Footer -->
<footer class="bg-[#F4F4F4] text-zinc-900 border-t border-zinc-200/80 pt-12 pb-8 select-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Brand intro strip -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-8 border-b border-zinc-200">
            <div class="flex items-center space-x-3">
                @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                    <img src="{{ $logo }}" class="h-9 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
                @else
                    <div class="flex items-center gap-1.5">
                        <span class="bg-[#FDD835] text-black font-black text-xl px-2 py-0.5 rounded-md">X</span>
                        <span class="font-black text-2xl text-zinc-950 tracking-tight">BUY</span>
                    </div>
                @endif
                <div class="h-5 w-px bg-zinc-300 hidden sm:block"></div>
                <span class="text-xs font-semibold text-zinc-600">
                    The safest community marketplace for electronics, PC components & verified gear.
                </span>
            </div>

            <!-- Escrow guarantee seal -->
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-xs font-bold text-zinc-800 self-start md:self-auto">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 2.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0 1 10 1.944ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0-7a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0V7Z" clip-rule="evenodd"/>
                </svg>
                <span>100% Escrow Secured • 7-Day Testing Window</span>
            </div>
        </div>

        <!-- 4-Column Directory -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 py-10 text-xs">
            
            <!-- Column 1: SHOP -->
            <div class="space-y-3">
                <h4 class="text-zinc-950 font-extrabold uppercase tracking-wider text-[11px]">Shop</h4>
                <ul class="space-y-2 text-zinc-600 font-medium">
                    <li><a href="/listings" class="hover:text-zinc-950 transition-colors">Trending PC Gear</a></li>
                    <li><a href="/listings" class="hover:text-zinc-950 transition-colors">Brands Directory</a></li>
                    <li><a href="/listings" class="hover:text-zinc-950 transition-colors">All Categories</a></li>
                    <li><a href="/p/deals" class="hover:text-zinc-950 transition-colors flex items-center gap-1"><span>Hot Deals & Drops</span> <span class="text-amber-500">🔥</span></a></li>
                    <li><a href="/p/how-it-works" class="hover:text-zinc-950 transition-colors">How X-Buy Works</a></li>
                    <li><a href="/p/gift-card-exchange" class="hover:text-zinc-950 transition-colors">Gift Cards & Wallet</a></li>
                    <li><a href="/p/coupons-and-promotions" class="hover:text-zinc-950 transition-colors">Coupons & Promo Codes</a></li>
                </ul>
            </div>

            <!-- Column 2: SELL -->
            <div class="space-y-3">
                <h4 class="text-zinc-950 font-extrabold uppercase tracking-wider text-[11px]">Sell</h4>
                <ul class="space-y-2 text-zinc-600 font-medium">
                    <li><a href="/dashboard/listings/create" class="hover:text-zinc-950 transition-colors font-bold text-amber-700">Sell on X-Buy (0% Fees)</a></li>
                    <li><a href="/p/packaging" class="hover:text-zinc-950 transition-colors">Packaging Best Practices</a></li>
                    <li><a href="/p/shipping" class="hover:text-zinc-950 transition-colors">Shipping Made Easy</a></li>
                    <li><a href="/p/getting-paid" class="hover:text-zinc-950 transition-colors">How Payouts Work</a></li>
                    <li><a href="/p/authenticate" class="hover:text-zinc-950 transition-colors">Hardware Authentication</a></li>
                    <li><a href="/p/seller-guidelines" class="hover:text-zinc-950 transition-colors">Seller Protection Policy</a></li>
                </ul>
            </div>

            <!-- Column 3: SUPPORT -->
            <div class="space-y-3">
                <h4 class="text-zinc-950 font-extrabold uppercase tracking-wider text-[11px]">Support</h4>
                <ul class="space-y-2 text-zinc-600 font-medium">
                    <li><a href="/p/faqs" class="hover:text-zinc-950 transition-colors">Help Center & FAQs</a></li>
                    <li><a href="/p/contact-us" class="hover:text-zinc-950 transition-colors">Contact Support Desk</a></li>
                    <li><a href="/p/buyer-protection" class="hover:text-zinc-950 transition-colors">Buyer Protection Guarantee</a></li>
                    <li><a href="/p/refund-policy" class="hover:text-zinc-950 transition-colors">Refunds & Returns Policy</a></li>
                    <li><a href="/p/safety-guidelines" class="hover:text-zinc-950 transition-colors">Prohibited Items & Safety</a></li>
                    <li><a href="/p/service-status" class="hover:text-zinc-950 transition-colors">Platform Status & Uptime</a></li>
                </ul>
            </div>

            <!-- Column 4: COMPANY & LEGAL -->
            <div class="space-y-3">
                <h4 class="text-zinc-950 font-extrabold uppercase tracking-wider text-[11px]">Company & Legal</h4>
                <ul class="space-y-2 text-zinc-600 font-medium">
                    <li><a href="/p/about-us" class="hover:text-zinc-950 transition-colors">About X-Buy Marketplace</a></li>
                    <li><a href="/p/careers" class="hover:text-zinc-950 transition-colors">Careers at X-Buy</a></li>
                    <li><a href="/p/terms-of-service" class="hover:text-zinc-950 transition-colors">Terms of Service</a></li>
                    <li><a href="/p/privacy-policy" class="hover:text-zinc-950 transition-colors">Privacy Policy</a></li>
                    <li><a href="/p/escrow-policy" class="hover:text-zinc-950 transition-colors">Escrow Financial Policy</a></li>
                    <li><a href="/p/cookie-preferences" class="hover:text-zinc-950 transition-colors">Cookie Preferences</a></li>
                    <li><a href="/p/licenses-and-disclosures" class="hover:text-zinc-950 transition-colors">Licenses & Disclosures</a></li>
                </ul>
            </div>

        </div>

        <!-- Payment & Logistics Badges Strip -->
        <div class="pt-8 border-t border-zinc-200 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            
            <!-- Payment methods -->
            <div class="space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 block">
                    Secure Payment Gateway Partners
                </span>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="h-7 px-2.5 bg-white border border-zinc-200 rounded flex items-center justify-center font-bold text-xs text-blue-700 shadow-2xs">
                        Razorpay
                    </div>
                    <div class="h-7 px-2.5 bg-white border border-zinc-200 rounded flex items-center justify-center font-bold text-xs text-emerald-700 shadow-2xs">
                        UPI • GPay • PhonePe
                    </div>
                    <div class="h-7 px-2.5 bg-white border border-zinc-200 rounded flex items-center justify-center font-bold text-xs text-indigo-900 shadow-2xs">
                        VISA
                    </div>
                    <div class="h-7 px-2.5 bg-white border border-zinc-200 rounded flex items-center justify-center font-bold text-xs text-red-600 shadow-2xs">
                        Mastercard
                    </div>
                    <div class="h-7 px-2.5 bg-white border border-zinc-200 rounded flex items-center justify-center font-bold text-xs text-zinc-800 shadow-2xs">
                        NetBanking
                    </div>
                </div>
            </div>

            <!-- Logistics partner -->
            <div class="space-y-2 md:text-right">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 block">
                    Insured Express Logistics
                </span>
                <div class="flex items-center md:justify-end gap-2 text-xs font-semibold text-zinc-800">
                    <span class="px-3 py-1 bg-white border border-zinc-200 rounded-md shadow-2xs">
                        🚚 Shiprocket Insured Network (Bluedart, Delhivery, DTDC)
                    </span>
                </div>
            </div>

        </div>

        <!-- Sub-footer copyright & metadata -->
        <div class="mt-8 pt-6 border-t border-zinc-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-zinc-500 font-medium">
            <p>&copy; {{ date('Y') }} {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }} Technologies Pvt. Ltd. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="/p/privacy-policy" class="hover:text-zinc-950 transition-colors">Privacy</a>
                <a href="/p/terms-of-service" class="hover:text-zinc-950 transition-colors">Terms</a>
                <a href="/p/escrow-policy" class="hover:text-zinc-950 transition-colors">Escrow</a>
                <a href="/p/contact-us" class="hover:text-zinc-950 transition-colors">Help</a>
            </div>
        </div>

    </div>
</footer>
