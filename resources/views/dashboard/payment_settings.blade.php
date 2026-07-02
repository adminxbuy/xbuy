@extends('layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            @include('dashboard.partials._sidebar', ['active' => 'payments'])

            <!-- Right Content Area -->
            <div class="flex-1 space-y-8" x-data="{ showCardModal: false, showPayoutModal: false }">
                <!-- Session messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
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

                <!-- Payment Options Section -->
                <div class="space-y-4">
                    <h3 class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">Payment options</h3>
                    
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                        <!-- UPI ID option (NEW) -->
                        <form action="/dashboard/settings/payments" method="POST" class="p-5 space-y-4">
                            @csrf
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-zinc-800">UPI ID</span>
                                @if($user->upi_id)
                                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Active: {{ $user->upi_id }}
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-zinc-400">No UPI linked</span>
                                @endif
                            </div>
                            
                            <div class="border-b border-zinc-200 focus-within:border-[#e6c019] pb-1">
                                <input type="text" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}" placeholder="Enter your UPI ID (e.g., username@bank)" class="w-full text-sm text-zinc-700 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
                                    Save UPI
                                </button>
                            </div>
                        </form>

                        <!-- Cards List -->
                        @if($user->cards && count($user->cards) > 0)
                            @foreach($user->cards as $index => $card)
                                <div class="p-5 flex items-center justify-between bg-zinc-50/50">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-16 h-8 bg-zinc-200 border border-zinc-300 rounded-sm flex items-center justify-center font-bold text-[9px] uppercase tracking-wider text-zinc-650 px-1 text-center leading-none">
                                            {{ $card['brand'] }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-zinc-850">•••• •••• •••• {{ $card['last4'] }}</span>
                                            <p class="text-[11px] text-zinc-400 mt-0.5">Expires {{ $card['expiry_date'] }} · {{ $card['cardholder_name'] }}</p>
                                        </div>
                                    </div>
                                    <form action="/dashboard/settings/payments/delete-card/{{ $index }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-zinc-400 hover:text-rose-600 transition-colors cursor-pointer bg-transparent border-none p-0 outline-none">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        @endif

                        <!-- Add card row -->
                        <div @click="showCardModal = true" class="p-5 flex items-center justify-between cursor-pointer hover:bg-zinc-50 transition-colors select-none">
                            <span class="text-sm font-bold text-zinc-850">Add card</span>
                            <i data-lucide="plus" class="w-5 h-5 text-zinc-400"></i>
                        </div>
                    </div>                <!-- Payout Options Section -->
                <div class="space-y-4">
                    <h3 class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">Payout options</h3>
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                        @if($user->sellerProfile && $user->sellerProfile->bank_account_number)
                            <div class="p-5 flex items-center justify-between bg-zinc-50/50">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-16 h-8 bg-zinc-200 border border-zinc-300 rounded-sm flex items-center justify-center font-bold text-[9px] uppercase tracking-wider text-zinc-650 px-1 text-center leading-none">
                                        {{ $user->sellerProfile->bank_name ?? 'BANK' }}
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-zinc-855">A/C: •••• {{ substr($user->sellerProfile->bank_account_number, -4) }}</span>
                                        <p class="text-[11px] text-zinc-400 mt-0.5">{{ $user->sellerProfile->bank_account_name }} · IFSC: {{ $user->sellerProfile->bank_ifsc }}</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Linked
                                </span>
                            </div>
                        @endif

                        <a href="/dashboard/settings/payments/bank-account" class="p-5 flex items-center justify-between cursor-pointer hover:bg-zinc-50 transition-colors select-none block">
                            <div class="space-y-1">
                                <span class="text-sm font-bold text-zinc-855">Add bank account for payouts</span>
                                <p class="text-xs text-zinc-400">Receive earnings directly when your items sell.</p>
                            </div>
                            <i data-lucide="plus" class="w-5 h-5 text-zinc-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Add Card Modal -->
                <div x-show="showCardModal" class="fixed inset-0 bg-black/55 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
                    <div class="bg-white rounded-md max-w-md w-full p-6 space-y-4 shadow-xl border border-zinc-200" @click.away="showCardModal = false">
                        <div class="text-center space-y-2">
                            <h3 class="text-lg font-bold text-zinc-900">Add Credit/Debit Card</h3>
                            <p class="text-xs text-zinc-500">Provide card details to link to your account.</p>
                        </div>

                        <form action="/dashboard/settings/payments/add-card" method="POST" class="space-y-4" 
                              x-data="{ 
                                  cardNumber: '',
                                  expiry: '',
                                  formatCard(e) {
                                      let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                                      let matches = v.match(/\d{4,16}/g);
                                      let match = matches && matches[0] || '';
                                      let parts = [];
                                      for (let i=0, len=match.length; i<len; i+=4) {
                                          parts.push(match.substring(i, i+4));
                                      }
                                      if (parts.length > 0) {
                                          this.cardNumber = parts.join(' ');
                                      } else {
                                          this.cardNumber = v;
                                      }
                                  },
                                  formatExpiry(e) {
                                      let v = e.target.value.replace(/\D/g, '');
                                      if (v.length > 2) {
                                          this.expiry = v.substring(0, 2) + '/' + v.substring(2, 4);
                                      } else {
                                          this.expiry = v;
                                      }
                                  }
                              }">
                            @csrf
                            
                            <div class="space-y-1">
                                <label for="cardholder_name" class="text-xs font-semibold text-zinc-600 block">Cardholder Name</label>
                                <input type="text" id="cardholder_name" name="cardholder_name" required placeholder="John Doe" class="w-full border border-zinc-300 rounded-sm px-3 py-2 text-sm focus:border-[#e6c019] focus:ring-1 focus:ring-[#e6c019] outline-none">
                            </div>

                            <div class="space-y-1">
                                <label for="card_number" class="text-xs font-semibold text-zinc-600 block">Card Number</label>
                                <input type="text" id="card_number" name="card_number" required placeholder="4111 2222 3333 4444" 
                                       x-model="cardNumber" @input="formatCard" maxlength="23"
                                       class="w-full border border-zinc-300 rounded-sm px-3 py-2 text-sm focus:border-[#e6c019] focus:ring-1 focus:ring-[#e6c019] outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label for="expiry_date" class="text-xs font-semibold text-zinc-600 block">Expiry Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" required placeholder="MM/YY" 
                                           x-model="expiry" @input="formatExpiry" maxlength="5"
                                           class="w-full border border-zinc-300 rounded-sm px-3 py-2 text-sm focus:border-[#e6c019] focus:ring-1 focus:ring-[#e6c019] outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label for="cvv" class="text-xs font-semibold text-zinc-600 block">CVV</label>
                                    <input type="text" id="cvv" name="cvv" required placeholder="123" maxlength="4"
                                           class="w-full border border-zinc-300 rounded-sm px-3 py-2 text-sm focus:border-[#e6c019] focus:ring-1 focus:ring-[#e6c019] outline-none">
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showCardModal = false" class="flex-1 px-4 py-2 border border-zinc-200 text-zinc-700 text-xs font-bold rounded-sm hover:bg-zinc-50 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm hover:bg-[#fdd835] transition-colors">
                                    Add Card
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
