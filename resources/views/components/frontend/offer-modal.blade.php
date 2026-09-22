@props(['listing'])

@php
    $price = (float) $listing->price;
    $minOffer = round($price * 0.80);
    $offer5 = round($price * 0.95);
    $offer10 = round($price * 0.90);
    $offer15 = round($price * 0.85);
@endphp

<!-- Mercari-Style 'Make an Offer' Negotiation Modal -->
<div x-data="{
    offerModalOpen: false,
    offerAmount: '',
    minOffer: {{ $minOffer }},
    listPrice: {{ $price }},
    submitted: false,
    errorMessage: '',
    setOffer(amount) {
        this.offerAmount = amount;
        this.errorMessage = '';
    },
    validateAndSubmit() {
        const val = Number(this.offerAmount);
        if (!val || isNaN(val)) {
            this.errorMessage = 'Please enter a valid offer amount.';
            return;
        }
        if (val < this.minOffer) {
            this.errorMessage = 'Minimum offer must be at least 80% of the listed price (₹' + this.minOffer.toLocaleString('en-IN') + ').';
            return;
        }
        if (val >= this.listPrice) {
            this.errorMessage = 'Offer amount should be less than the current listed price.';
            return;
        }
        
        // Submit offer via fetch
        this.errorMessage = '';
        @if(auth()->check())
            fetch('/api/buyer/tickets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    subject: 'Price Offer: ₹' + val + ' on {{ addslashes($listing->title) }}',
                    category: 'general',
                    description: 'Buyer {{ auth()->user()->name }} made a formal offer of ₹' + val + ' for Listing #{{ $listing->id }} (Listed: ₹{{ $price }}). Valid for 24 hours.'
                })
            }).catch(() => {});
            this.submitted = true;
        @else
            window.location.href = '{{ route('login') }}';
        @endif
    }
}" 
@open-offer-modal.window="offerModalOpen = true; submitted = false; errorMessage = ''; offerAmount = {{ $offer10 }};"
@keydown.window.escape="offerModalOpen = false"
x-cloak>

    <!-- Modal Backdrop -->
    <div x-show="offerModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[100]"
         @click="offerModalOpen = false"
         style="display: none;"></div>

    <!-- Modal Dialog -->
    <div x-show="offerModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[101] flex items-center justify-center p-4"
         style="display: none;">
        
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-zinc-200 overflow-hidden"
             @click.away="offerModalOpen = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-zinc-950">Make an Offer</h3>
                    <p class="text-xs text-zinc-500 truncate max-w-[280px]">{{ $listing->title }}</p>
                </div>
                <button type="button" @click="offerModalOpen = false" class="p-1.5 text-zinc-400 hover:text-black rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                
                <template x-if="!submitted">
                    <div class="space-y-5">
                        <!-- Listed Price summary -->
                        <div class="flex items-center justify-between bg-zinc-100/70 p-3 rounded-xl border border-zinc-200/80">
                            <span class="text-xs text-zinc-600 font-medium">Original Listed Price:</span>
                            <span class="text-base font-extrabold text-zinc-950">₹{{ number_format($price) }}</span>
                        </div>

                        <!-- Quick Discount Chips -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">
                                Quick Discount Offers:
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" 
                                        @click="setOffer({{ $offer5 }})"
                                        :class="offerAmount == {{ $offer5 }} ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white hover:border-zinc-400 text-zinc-800'"
                                        class="p-2.5 rounded-xl border text-center transition-all cursor-pointer">
                                    <span class="block text-[11px] font-bold">5% Off</span>
                                    <span class="block text-xs font-extrabold mt-0.5">₹{{ number_format($offer5) }}</span>
                                </button>

                                <button type="button" 
                                        @click="setOffer({{ $offer10 }})"
                                        :class="offerAmount == {{ $offer10 }} ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white hover:border-zinc-400 text-zinc-800'"
                                        class="p-2.5 rounded-xl border text-center transition-all cursor-pointer">
                                    <span class="block text-[11px] font-bold">10% Off</span>
                                    <span class="block text-xs font-extrabold mt-0.5">₹{{ number_format($offer10) }}</span>
                                </button>

                                <button type="button" 
                                        @click="setOffer({{ $offer15 }})"
                                        :class="offerAmount == {{ $offer15 }} ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white hover:border-zinc-400 text-zinc-800'"
                                        class="p-2.5 rounded-xl border text-center transition-all cursor-pointer">
                                    <span class="block text-[11px] font-bold">15% Off</span>
                                    <span class="block text-xs font-extrabold mt-0.5">₹{{ number_format($offer15) }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Offer Amount Input -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-zinc-700 uppercase tracking-wider">Custom Offer Amount</label>
                                <span class="text-[10px] text-zinc-400 font-semibold">Min 80%: ₹{{ number_format($minOffer) }}</span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3.5 top-3 text-sm font-bold text-zinc-400">₹</span>
                                <input type="number" 
                                       x-model="offerAmount" 
                                       placeholder="Enter your offer" 
                                       class="w-full pl-8 pr-4 py-2.5 bg-zinc-50 border border-zinc-300 rounded-xl text-base font-extrabold text-zinc-950 outline-none focus:border-zinc-900 focus:bg-white transition-all">
                            </div>
                            <p x-show="errorMessage" x-text="errorMessage" class="text-xs text-red-600 font-semibold mt-1.5" style="display: none;"></p>
                        </div>

                        <!-- 24-Hour Notice Banner -->
                        <div class="p-3 bg-amber-50 border border-amber-200/80 rounded-xl flex items-start gap-2.5 text-xs text-amber-950 leading-relaxed">
                            <span class="text-sm shrink-0">ℹ️</span>
                            <span>Offers are valid for <strong>24 hours</strong>. If the seller accepts, you will receive a direct notification and your order will be reserved in Escrow.</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="button" 
                                @click="validateAndSubmit"
                                class="w-full py-3.5 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm rounded-xl shadow-sm transition-transform active:scale-98 cursor-pointer">
                            Send Offer to Seller
                        </button>
                    </div>
                </template>

                <!-- Success Confirmation View -->
                <template x-if="submitted">
                    <div class="py-8 text-center space-y-4">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-7 h-7 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-zinc-950">Offer Sent Successfully!</h4>
                        <p class="text-xs text-zinc-600 max-w-xs mx-auto leading-relaxed">
                            Your offer of <strong class="text-zinc-950" x-text="'₹' + Number(offerAmount).toLocaleString('en-IN')"></strong> has been delivered to the seller. We will notify you via email and notification once they respond.
                        </p>
                        <button type="button" 
                                @click="offerModalOpen = false" 
                                class="mt-2 px-6 py-2.5 bg-zinc-900 text-white font-bold text-xs rounded-full hover:bg-black">
                            Back to Listing
                        </button>
                    </div>
                </template>

            </div>

        </div>
    </div>
</div>
