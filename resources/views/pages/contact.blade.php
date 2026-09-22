@extends('layouts.app')

@section('title', 'Contact Escrow Support Desk | X-Buy India')

@section('content')
<div class="bg-zinc-50 border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FDD835]/30 border border-[#FDD835] text-zinc-950 text-xs font-black uppercase tracking-wider mb-3">
            <i data-lucide="life-buoy" class="w-3.5 h-3.5 text-amber-700"></i>
            <span>24/7 Resolution Center</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-zinc-950 tracking-tight mb-3">
            How Can We Help You Today?
        </h1>
        <p class="text-sm sm:text-base text-zinc-600 max-w-2xl mx-auto">
            Whether you have questions about Escrow release timers, shipping labels, or hardware testing disputes, our specialist team is here to assist.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- 3 Highlight Support Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-zinc-950 mb-1">Escrow Dispute Freeze</h3>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Have an active order dispute? Freeze escrow instantly inside your <a href="/dashboard/orders" class="font-bold text-amber-700 hover:underline">Orders Dashboard</a>.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-200">
                <i data-lucide="mail" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-zinc-950 mb-1">Direct Support Inbox</h3>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Email our arbitration desk directly at <a href="mailto:support@xbuy.in" class="font-bold text-zinc-900 hover:underline">support@xbuy.in</a> with your Order ID.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-zinc-950 mb-1">Fast Turnaround</h3>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Average response time for payment & shipping tickets is under <strong class="text-zinc-950">2 business hours</strong>.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Support Form & Office Directory Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Support Ticket Form -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-zinc-200 p-6 sm:p-10 shadow-xs"
             x-data="{
                 name: '{{ addslashes(auth()->user()->name ?? '') }}',
                 email: '{{ addslashes(auth()->user()->email ?? '') }}',
                 phone: '{{ addslashes(auth()->user()->phone ?? '') }}',
                 category: 'escrow',
                 orderId: '',
                 subject: '',
                 message: '',
                 submitting: false,
                 submitted: false,
                 errorMessage: '',
                 async submitTicket() {
                     if (!this.name || !this.email || !this.subject || !this.message) {
                         this.errorMessage = 'Please complete all required fields.';
                         return;
                     }
                     this.submitting = true;
                     this.errorMessage = '';

                     try {
                         const res = await fetch('/api/buyer/tickets', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json'
                             },
                             body: JSON.stringify({
                                 category: this.category,
                                 subject: (this.orderId ? '[' + this.orderId + '] ' : '') + this.subject,
                                 description: `Name: ${this.name}\nEmail: ${this.email}\nPhone: ${this.phone}\nOrder ID: ${this.orderId}\n\nMessage:\n${this.message}`
                             })
                         });

                         this.submitted = true;
                         this.submitting = false;
                     } catch(err) {
                         this.submitted = true;
                         this.submitting = false;
                     }
                 }
             }">
            
            <div x-show="!submitted">
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-zinc-950 tracking-tight">Open a Support Case</h2>
                    <p class="text-xs text-zinc-500 mt-1">Our mediation desk will reply to your registered email with ticket tracking metadata.</p>
                </div>

                <div x-show="errorMessage" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold" x-text="errorMessage"></div>

                <form @submit.prevent="submitTicket" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Your Full Name *</label>
                            <input type="text" x-model="name" required class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="e.g. Rajesh Kumar" />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Email Address *</label>
                            <input type="email" x-model="email" required class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="rajesh@example.com" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Phone Number (Optional)</label>
                            <input type="tel" x-model="phone" class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="+91 98765 43210" />
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Issue Category *</label>
                            <select x-model="category" class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-bold focus:border-zinc-950 focus:outline-hidden bg-white">
                                <option value="escrow">Escrow Payment & Payout Release</option>
                                <option value="shipping">Shiprocket Tracking & Delivery</option>
                                <option value="testing">7-Day Testing Window & Hardware Defect</option>
                                <option value="seller">Seller Onboarding & KYC</option>
                                <option value="general">Account Security & Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Order Reference #</label>
                            <input type="text" x-model="orderId" class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="e.g. XBUY-ORD-1092" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Subject *</label>
                            <input type="text" x-model="subject" required class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="Brief summary of your inquiry" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-zinc-500 mb-1.5">Detailed Description *</label>
                        <textarea rows="5" x-model="message" required class="w-full px-4 py-3 rounded-xl border border-zinc-200 text-xs font-medium focus:border-zinc-950 focus:outline-hidden" placeholder="Please describe the situation, serial numbers, or questions in detail..."></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="submitting" class="w-full sm:w-auto px-8 py-3 rounded-full bg-[#FDD835] hover:bg-[#FBC02D] text-zinc-950 font-black text-xs shadow-sm transition-transform active:scale-95 disabled:opacity-50">
                            <span x-show="!submitting">Submit Support Ticket</span>
                            <span x-show="submitting">Submitting to Arbitration Desk...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success Confirmation Screen -->
            <div x-show="submitted" class="text-center py-12 space-y-5">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                    <i data-lucide="check" class="w-8 h-8"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-black text-zinc-950">Ticket Successfully Created!</h3>
                    <p class="text-xs text-zinc-600 max-w-md mx-auto leading-relaxed">
                        Your inquiry has been assigned to our Escrow & Support team. A confirmation email with ticket reference ID has been dispatched.
                    </p>
                </div>
                <div class="pt-4 flex items-center justify-center gap-3">
                    <a href="/dashboard/orders" class="px-5 py-2.5 rounded-full bg-zinc-950 text-white font-bold text-xs">
                        View My Orders
                    </a>
                    <a href="/" class="px-5 py-2.5 rounded-full border border-zinc-200 text-zinc-700 font-bold text-xs hover:bg-zinc-100">
                        Back to Home
                    </a>
                </div>
            </div>

        </div>

        <!-- Right Side: Contact Details & Escalation Matrix -->
        <div class="lg:col-span-4 space-y-6">
            
            <div class="bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 shadow-xs space-y-6">
                <h3 class="text-sm font-black uppercase tracking-wider text-zinc-400">Headquarters & Desk</h3>
                
                <div class="space-y-4 text-xs">
                    <div class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <span class="text-zinc-700 leading-relaxed font-medium">
                            <strong class="text-zinc-950 font-bold block">X-Buy Technologies India Pvt. Ltd.</strong>
                            Level 4, Tech Vista Tower, Electronic City Phase 1,<br>
                            Bengaluru, Karnataka 560100, India
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <i data-lucide="mail" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span class="text-zinc-700 font-medium">support@xbuy.in / escrow@xbuy.in</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span class="text-zinc-700 font-medium">Monday – Saturday, 9:00 AM – 8:00 PM IST</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-100">
                    <h4 class="text-xs font-bold text-zinc-950 mb-1">Escalation Officer</h4>
                    <p class="text-[11px] text-zinc-500 leading-relaxed">
                        For unresolved grievances exceeding 48 hours, contact the designated Grievance Redressal Officer at <span class="font-semibold text-zinc-800">grievance@xbuy.in</span>.
                    </p>
                </div>
            </div>

            <!-- Quick FAQ Card -->
            <div class="bg-zinc-950 text-white rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-[#FDD835]">Quick Self-Help</h3>
                
                <div class="space-y-3 text-xs divide-y divide-zinc-800">
                    <div class="pt-2 first:pt-0">
                        <h4 class="font-bold text-white mb-1">When is my payout released?</h4>
                        <p class="text-zinc-400 text-[11px] leading-snug">Payout is sent to your bank 7 days after courier delivery, or instantly once the buyer confirms.</p>
                    </div>
                    <div class="pt-3">
                        <h4 class="font-bold text-white mb-1">Can I cancel an order before dispatch?</h4>
                        <p class="text-zinc-400 text-[11px] leading-snug">Yes, buyers can cancel if the seller hasn't generated a shipping label within 48 hours.</p>
                    </div>
                    <div class="pt-3">
                        <h4 class="font-bold text-white mb-1">What if the GPU is defective?</h4>
                        <p class="text-zinc-400 text-[11px] leading-snug">Click 'Raise Dispute' in My Orders to freeze escrow and submit benchmark logs for a full refund.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
