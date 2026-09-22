<!-- Mercari-Style Slide-Out Cart Drawer -->
<div x-data="{
    cartOpen: false,
    cartItems: [],
    couponCode: '',
    couponDiscount: 0,
    init() {
        // Load initial cart from localStorage
        try {
            this.cartItems = JSON.parse(localStorage.getItem('xbuy_cart') || '[]');
        } catch(e) {
            this.cartItems = [];
        }
        window.addEventListener('open-cart', () => { this.cartOpen = true; });
        window.addEventListener('cart-updated', () => {
            try {
                this.cartItems = JSON.parse(localStorage.getItem('xbuy_cart') || '[]');
            } catch(e) {}
        });
    },
    saveCart() {
        localStorage.setItem('xbuy_cart', JSON.stringify(this.cartItems));
        window.dispatchEvent(new CustomEvent('cart-updated'));
    },
    removeItem(index) {
        this.cartItems.splice(index, 1);
        this.saveCart();
    },
    updateQuantity(index, delta) {
        const item = this.cartItems[index];
        if (!item) return;
        const newQty = (item.quantity || 1) + delta;
        if (newQty <= 0) {
            this.removeItem(index);
        } else {
            item.quantity = newQty;
            this.saveCart();
        }
    },
    get subtotal() {
        return this.cartItems.reduce((sum, item) => sum + (Number(item.price) * (item.quantity || 1)), 0);
    },
    get shippingFee() {
        return this.cartItems.length > 0 ? (this.cartItems.some(i => i.shipping_fee > 0) ? 120 : 0) : 0;
    },
    get protectionFee() {
        return this.cartItems.length > 0 ? 85 : 0;
    },
    get grandTotal() {
        return Math.max(0, this.subtotal + this.shippingFee + this.protectionFee - this.couponDiscount);
    },
    applyCoupon() {
        if (!this.couponCode.trim()) return;
        if (this.couponCode.toUpperCase() === 'XBUY10') {
            this.couponDiscount = Math.round(this.subtotal * 0.1);
        } else if (this.couponCode.toUpperCase() === 'FIRSTBUY') {
            this.couponDiscount = 200;
        } else {
            alert('Invalid coupon code. Try XBUY10 or FIRSTBUY');
        }
    },
    proceedToCheckout() {
        if (this.cartItems.length === 0) return;
        const firstItem = this.cartItems[0];
        window.location.href = `/checkout?listing_id=${firstItem.id}`;
    }
}" 
x-cloak
@keydown.window.escape="cartOpen = false">

    <!-- Backdrop -->
    <div x-show="cartOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[100]"
         @click="cartOpen = false"
         style="display: none;"></div>

    <!-- Drawer Panel (Slides in from Right) -->
    <div x-show="cartOpen"
         x-transition:enter="transition-transform ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-[101] flex flex-col border-l border-zinc-200"
         style="display: none;">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50/70">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-zinc-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <h3 class="text-base font-extrabold text-zinc-950 tracking-tight">Your Cart</h3>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-yellow-400 text-black" x-text="cartItems.length"></span>
            </div>
            <button @click="cartOpen = false" class="p-2 text-zinc-400 hover:text-zinc-950 rounded-lg hover:bg-zinc-100 transition-colors" aria-label="Close cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- 7-Day Escrow Notification Tag -->
        <div class="bg-amber-50/80 border-b border-amber-200/60 px-5 py-2.5 flex items-center gap-2.5 text-xs text-amber-950">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
            <span><strong>X-Buy Escrow Protected:</strong> Funds released only after 7-day inspection.</span>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar">
            
            <!-- Empty State -->
            <template x-if="cartItems.length === 0">
                <div class="py-16 text-center space-y-3 flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-zinc-900">Your cart is empty</h4>
                    <p class="text-xs text-zinc-500 max-w-xs">Discover verified graphics cards, processors, and PC components with 100% escrow protection.</p>
                    <a href="/listings" @click="cartOpen = false" class="mt-2 inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-xs shadow-xs transition-transform active:scale-95">
                        Start Shopping
                    </a>
                </div>
            </template>

            <!-- Populated Cart Items -->
            <template x-for="(item, index) in cartItems" :key="item.id || index">
                <div class="flex gap-3.5 p-3 rounded-xl border border-zinc-200/80 bg-white hover:border-zinc-300 transition-all">
                    <!-- Image -->
                    <a :href="'/listings/' + item.slug" class="w-20 h-20 rounded-lg bg-zinc-100 overflow-hidden shrink-0 border border-zinc-150">
                        <img :src="item.image || '/website_assets/images/placeholder.png'" :alt="item.title" class="w-full h-full object-cover">
                    </a>

                    <!-- Details -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <a :href="'/listings/' + item.slug" class="text-xs font-bold text-zinc-900 line-clamp-2 leading-snug hover:underline" x-text="item.title"></a>
                                <button @click="removeItem(index)" class="text-zinc-400 hover:text-red-600 transition-colors p-1" aria-label="Remove item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] text-zinc-500 font-medium" x-text="item.seller_name ? 'Sold by ' + item.seller_name : 'Verified Seller'"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-zinc-100 mt-2">
                            <span class="text-sm font-extrabold text-zinc-950" x-text="'₹' + Number(item.price).toLocaleString('en-IN')"></span>
                            
                            <!-- Quantity selector -->
                            <div class="flex items-center border border-zinc-200 rounded-lg overflow-hidden text-xs">
                                <button @click="updateQuantity(index, -1)" class="px-2 py-0.5 hover:bg-zinc-100 text-zinc-600 font-bold transition-colors">-</button>
                                <span class="px-2 py-0.5 text-zinc-900 font-semibold" x-text="item.quantity || 1"></span>
                                <button @click="updateQuantity(index, 1)" class="px-2 py-0.5 hover:bg-zinc-100 text-zinc-600 font-bold transition-colors">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Checkout Accordion -->
        <template x-if="cartItems.length > 0">
            <div class="p-5 border-t border-zinc-200 bg-zinc-50 space-y-3 shrink-0">
                
                <!-- Coupon input -->
                <div class="flex gap-2">
                    <input type="text" 
                           x-model="couponCode" 
                           placeholder="Discount code (e.g. XBUY10)" 
                           class="flex-1 bg-white border border-zinc-300 rounded-lg px-3 py-2 text-xs text-zinc-900 uppercase tracking-wider focus:outline-none focus:border-zinc-900">
                    <button @click="applyCoupon" 
                            class="px-4 py-2 bg-zinc-900 hover:bg-black text-white text-xs font-bold rounded-lg transition-colors">
                        Apply
                    </button>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-1.5 text-xs text-zinc-600 pt-1">
                    <div class="flex justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-zinc-900" x-text="'₹' + subtotal.toLocaleString('en-IN')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shiprocket Insured Shipping</span>
                        <span class="font-bold text-emerald-600" x-text="shippingFee === 0 ? 'FREE' : '₹' + shippingFee"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Buyer Escrow Protection Fee</span>
                        <span class="font-bold text-zinc-900" x-text="'₹' + protectionFee"></span>
                    </div>
                    <template x-if="couponDiscount > 0">
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Promo Discount</span>
                            <span x-text="'-₹' + couponDiscount.toLocaleString('en-IN')"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-sm font-extrabold text-zinc-950 pt-2 border-t border-zinc-200">
                        <span>Grand Total</span>
                        <span class="text-base font-black text-zinc-950" x-text="'₹' + grandTotal.toLocaleString('en-IN')"></span>
                    </div>
                </div>

                <!-- Checkout CTA -->
                <button @click="proceedToCheckout" 
                        class="w-full py-3 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm rounded-xl shadow-sm transition-transform active:scale-98 flex items-center justify-center gap-2">
                    <span>Proceed to Checkout</span>
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </template>

    </div>
</div>
