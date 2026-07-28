@extends('layouts.admin')

@section('title', 'Seller profile')
@section('page_title', 'Seller Profile Management')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.sellers') }}"
            class="inline-flex items-center space-x-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Directory</span>
        </a>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Profile details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between pb-6 border-b border-border">
                    <div class="flex items-center space-x-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($seller->user->name) }}&background=e4e4e7&color=71717a"
                            class="w-14 h-14 rounded-xl" alt="avatar">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h2 class="text-xl font-bold text-foreground">{{ $seller->user->name }}</h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                                    @if($seller->status === 'active') bg-emerald-50 text-emerald-705 border border-emerald-200/60
                                    @elseif($seller->status === 'suspended') bg-amber-100 text-amber-800
                                    @elseif($seller->status === 'banned') bg-rose-50 text-rose-705 border border-rose-200/60
                                    @else bg-muted text-foreground @endif">
                                    {{ ucfirst($seller->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">Shop: <span
                                    class="font-medium text-foreground">{{ $seller->shop_name }}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end">
                        @if($seller->status !== 'banned')
                            <div title="{{ $seller->badge_label }} Badge"
                                class="transition-transform hover:scale-110 hover:rotate-6 cursor-help origin-bottom-right">
                                @if($seller->badge_level === 'basic')
                                    <img src="{{ asset('website_assets/images/basic.png') }}"
                                        class="w-24 h-24 object-contain drop-shadow-lg" alt="Basic Seller">
                                @elseif($seller->badge_level === 'verified')
                                    <img src="{{ asset('website_assets/images/verified.png') }}"
                                        class="w-24 h-24 object-contain drop-shadow-lg" alt="Verified Seller">
                                @elseif($seller->badge_level === 'fulfilled')
                                    <img src="{{ asset('website_assets/images/FULFILLED.png') }}"
                                        class="w-24 h-24 object-contain drop-shadow-lg" alt="Fulfilled Seller">
                                @else
                                    <i data-lucide="{{ $seller->badge_icon }}" class="w-24 h-24 drop-shadow-lg"
                                        style="color: {{ $seller->badge_color }};"></i>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Profile Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 text-sm">
                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Email Address</p>
                        <p class="text-foreground mt-1 font-medium">{{ $seller->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Phone Number</p>
                        <p class="text-foreground mt-1 font-medium">{{ $seller->user->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Shop Slug</p>
                        <p class="text-foreground mt-1 font-medium">{{ $seller->shop_slug }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Business Address</p>
                        <p class="text-foreground mt-1 font-medium">{{ $seller->shop_city }}, {{ $seller->shop_state }} -
                            {{ $seller->shop_pincode }}</p>
                    </div>
                </div>
            </div>

            <!-- Decrypted KYC Documents Panel -->
            @php
                $aadhaarRaw = $seller->aadhaar_number ?: 'N/A';
                if ($aadhaarRaw !== 'N/A' && strlen($aadhaarRaw) > 3) {
                    $aadhaarLast3 = substr($aadhaarRaw, -3);
                    $aadhaarMasked = str_repeat('X', strlen($aadhaarRaw) - 3) . $aadhaarLast3;
                } else {
                    $aadhaarMasked = $aadhaarRaw;
                }

                $panRaw = $seller->pan_number ?: 'N/A';
                if ($panRaw !== 'N/A' && strlen($panRaw) > 3) {
                    $panLast3 = substr($panRaw, -3);
                    $panMasked = str_repeat('X', strlen($panRaw) - 3) . $panLast3;
                } else {
                    $panMasked = $panRaw;
                }

                $bankRaw = $seller->bank_account_number ?: 'N/A';
                if ($bankRaw !== 'N/A' && strlen($bankRaw) > 4) {
                    $bankStart4 = substr($bankRaw, 0, 4);
                    $bankMasked = $bankStart4 . str_repeat('X', strlen($bankRaw) - 4);
                } else {
                    $bankMasked = $bankRaw;
                }

                $ifscRaw = $seller->bank_ifsc ?: 'N/A';
                if ($ifscRaw !== 'N/A' && strlen($ifscRaw) > 4) {
                    $ifscStart4 = substr($ifscRaw, 0, 4);
                    $ifscMasked = $ifscStart4 . str_repeat('X', strlen($ifscRaw) - 4);
                } else {
                    $ifscMasked = $ifscRaw;
                }

                $upiRaw = $seller->upi_id ?: 'N/A';
                if ($upiRaw !== 'N/A') {
                    $atPos = strpos($upiRaw, '@');
                    if ($atPos !== false && $atPos > 3) {
                        $upiMasked = substr($upiRaw, 0, 3) . str_repeat('X', $atPos - 3) . substr($upiRaw, $atPos);
                    } else if ($atPos !== false) {
                        $upiMasked = str_repeat('X', $atPos) . substr($upiRaw, $atPos);
                    } else {
                        $upiMasked = str_repeat('X', strlen($upiRaw));
                    }
                } else {
                    $upiMasked = 'N/A';
                }
            @endphp
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm" x-data="{
                activeDoc: null,
                showBankAcc: false,
                showIfsc: false,
                showUpi: false,
                branchName: 'Fetching details...',
                init() {
                    let ifsc = '{{ $seller->bank_ifsc }}';
                    if (ifsc && ifsc !== 'N/A' && ifsc !== '') {
                        fetch('https://ifsc.razorpay.com/' + ifsc.trim())
                            .then(res => {
                                if (!res.ok) throw new Error('Not found');
                                return res.json();
                            })
                            .then(data => {
                                this.branchName = data.BANK + ' (' + data.BRANCH + ')';
                            })
                            .catch(err => {
                                this.branchName = 'Unknown (IFSC: ' + ifsc + ')';
                            });
                    } else {
                        this.branchName = 'N/A';
                    }
                }
            }">
                <h3 class="font-bold text-foreground text-lg mb-6 flex items-center">
                    <i data-lucide="shield-alert" class="w-5 h-5 mr-2 text-muted-foreground"></i>
                    KYC Verification Documents (Secured & Decrypted)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Aadhaar Number</p>
                        <div class="flex items-center mt-1">
                            <span
                                class="font-semibold text-foreground font-mono tracking-wider bg-muted px-3 py-2 rounded-lg border border-border inline-block"
                                x-text="activeDoc === 'aadhaar' ? '{{ $aadhaarRaw }}' : '{{ $aadhaarMasked }}'">
                            </span>
                            @if($seller->aadhaar_number)
                                <button type="button" @click="activeDoc = (activeDoc === 'aadhaar' ? null : 'aadhaar')"
                                    class="ml-2.5 p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                                    title="Toggle Aadhaar View">
                                    <i data-lucide="eye" x-show="activeDoc !== 'aadhaar'" class="w-4.5 h-4.5"></i>
                                    <i data-lucide="eye-off" x-show="activeDoc === 'aadhaar'" class="w-4.5 h-4.5" x-cloak></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">PAN Number</p>
                        <div class="flex items-center mt-1">
                            <span
                                class="font-semibold text-foreground font-mono tracking-wider bg-muted px-3 py-2 rounded-lg border border-border inline-block uppercase"
                                x-text="activeDoc === 'pan' ? '{{ $panRaw }}' : '{{ $panMasked }}'">
                            </span>
                            @if($seller->pan_number)
                                <button type="button" @click="activeDoc = (activeDoc === 'pan' ? null : 'pan')"
                                    class="ml-2.5 p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                                    title="Toggle PAN View">
                                    <i data-lucide="eye" x-show="activeDoc !== 'pan'" class="w-4.5 h-4.5"></i>
                                    <i data-lucide="eye-off" x-show="activeDoc === 'pan'" class="w-4.5 h-4.5" x-cloak></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="sm:col-span-2 border-t border-border pt-6">
                        <h4 class="font-semibold text-foreground mb-4 flex items-center">
                            <i data-lucide="wallet" class="w-4 h-4 mr-2"></i>
                            Payout Method Details
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Bank Account Number
                                </p>
                                <div class="flex items-center mt-1">
                                    <span class="text-foreground font-mono font-medium"
                                        x-text="showBankAcc ? '{{ $bankRaw }}' : '{{ $bankMasked }}'">
                                    </span>
                                    @if($seller->bank_account_number)
                                        <button type="button" @click="showBankAcc = !showBankAcc"
                                            class="ml-2.5 p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                                            title="Toggle Account Number View">
                                            <i data-lucide="eye" x-show="!showBankAcc" class="w-4 h-4"></i>
                                            <i data-lucide="eye-off" x-show="showBankAcc" class="w-4 h-4" x-cloak></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Bank IFSC Code</p>
                                <div class="flex items-center mt-1">
                                    <span class="text-foreground font-mono font-medium uppercase"
                                        x-text="showIfsc ? '{{ $ifscRaw }}' : '{{ $ifscMasked }}'">
                                    </span>
                                    @if($seller->bank_ifsc)
                                        <button type="button" @click="showIfsc = !showIfsc"
                                            class="ml-2.5 p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                                            title="Toggle IFSC View">
                                            <i data-lucide="eye" x-show="!showIfsc" class="w-4 h-4"></i>
                                            <i data-lucide="eye-off" x-show="showIfsc" class="w-4 h-4" x-cloak></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Bank / Branch Name
                                </p>
                                <p class="text-foreground mt-1 font-medium text-sm" x-text="branchName"></p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">UPI ID</p>
                                <div class="flex items-center mt-1">
                                    <span class="text-foreground font-mono font-medium"
                                        x-text="showUpi ? '{{ $upiRaw }}' : '{{ $upiMasked }}'">
                                    </span>
                                    @if($seller->upi_id)
                                        <button type="button" @click="showUpi = !showUpi"
                                            class="ml-2.5 p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                                            title="Toggle UPI View">
                                            <i data-lucide="eye" x-show="!showUpi" class="w-4 h-4"></i>
                                            <i data-lucide="eye-off" x-show="showUpi" class="w-4 h-4" x-cloak></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Direct Chat System -->
            <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden flex flex-col h-[400px]"
                x-data="sellerChatManager()">
                <!-- Chat Header -->
                <div class="px-6 py-4 border-b border-border bg-muted/50 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground hover:bg-primary/90/15 flex items-center justify-center text-foreground">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground text-sm">Direct Conversation</h3>
                            <p class="text-[10px] text-muted-foreground font-medium">Chat history with {{ $seller->user->name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="fetchMessages()"
                        class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
                        title="Refresh Chat">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Messages Box -->
                <div id="chat-messages-box" class="flex-1 overflow-y-auto p-6 space-y-4 scrollbar-thin">
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex flex-col" :class="msg.is_admin ? 'items-end' : 'items-start'">
                            <div class="flex items-center space-x-1 mb-1">
                                <span class="text-[10px] font-bold text-muted-foreground" x-text="msg.sender_name"></span>
                                <span class="text-[9px] text-muted-foreground" x-text="msg.time"></span>
                            </div>
                            <div class="max-w-[75%] rounded-xl px-4 py-2.5 text-sm font-medium shadow-sm border animate-fade-in"
                                :class="msg.is_admin ? 'bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground border-[#ffd747]/25 rounded-tr-none' : 'bg-muted text-foreground border-border/60 rounded-tl-none'">
                                <p class="whitespace-pre-wrap leading-relaxed break-words" x-text="msg.message"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="messages.length === 0" class="flex flex-col items-center justify-center py-12 text-center"
                        x-cloak>
                        <div
                            class="w-10 h-10 bg-muted border border-border/60 rounded-xl flex items-center justify-center text-muted-foreground mb-2">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                        </div>
                        <p class="text-xs text-muted-foreground font-semibold">No messages yet</p>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Send a message below to start the conversation.</p>
                    </div>
                </div>

                <!-- Chat Input Area -->
                <div class="p-4 border-t border-border bg-muted/30">
                    <form @submit.prevent="sendMessage()" class="flex gap-2">
                        <input type="text" x-model="newMessage" placeholder="Type a message..." required
                            class="flex-1 px-4 py-2.5 border border-border rounded-xl text-sm focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card transition-all">
                        <button type="submit" :disabled="sending"
                            class="bg-primary hover:bg-primary/90 disabled:bg-muted text-primary-foreground font-bold px-5 py-2.5 rounded-xl text-sm shadow-sm transition-all flex items-center space-x-2">
                            <span x-show="!sending">Send</span>
                            <span x-show="sending" x-cloak>Sending...</span>
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function sellerChatManager() {
                return {
                    messages: <?php echo json_encode($messages->map(function ($msg) {
        return [
            'id' => $msg->id,
            'sender_id' => $msg->sender_id,
            'sender_name' => $msg->sender->name,
            'message' => $msg->message,
            'is_admin' => $msg->sender->role === 'admin',
            'time' => $msg->created_at->format('M d, H:i'),
        ];
    })->toArray()); ?>,
                    newMessage: '',
                    sending: false,
                    init() {
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                        // Poll for new messages every 5 seconds
                        setInterval(() => {
                            this.fetchMessages();
                        }, 5000);
                    },
                    scrollToBottom() {
                        const box = document.getElementById('chat-messages-box');
                        if (box) {
                            box.scrollTop = box.scrollHeight;
                        }
                    },
                    async fetchMessages() {
                        try {
                            let res = await fetch("{{ route('admin.sellers.chat.index', $seller->id) }}");
                            let result = await res.json();
                            if (result.success) {
                                this.messages = result.data;
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            }
                        } catch (e) {
                            console.error('Failed to load chat messages', e);
                        }
                    },
                    async sendMessage() {
                        if (!this.newMessage.trim() || this.sending) return;
                        this.sending = true;
                        try {
                            let res = await fetch("{{ route('admin.sellers.chat.store', $seller->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ message: this.newMessage })
                            });
                            let result = await res.json();
                            if (result.success) {
                                this.messages.push(result.data);
                                this.newMessage = '';
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            }
                        } catch (e) {
                            console.error('Failed to send message', e);
                        } finally {
                            this.sending = false;
                        }
                    }
                };
            }
        </script>

        <!-- Sidebar Verification Action Panel -->
        <div class="space-y-6">
            <!-- Verification Card -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-foreground text-lg mb-4">KYC Review Board</h3>

                <div class="mb-6">
                    <span class="px-3 py-1.5 text-xs rounded-full font-semibold inline-block
                        @if($seller->kyc_status === 'pending') bg-amber-50 text-amber-705 border border-amber-200/60
                        @elseif($seller->kyc_status === 'approved') bg-emerald-50 text-emerald-705 border border-emerald-200/60
                        @else bg-rose-50 text-rose-705 border border-rose-200/60 @endif">
                        Current Status: {{ ucfirst($seller->kyc_status) }}
                    </span>

                    @if($seller->kyc_rejection_reason)
                        <p class="text-xs text-rose-600 font-semibold mt-3 p-3 bg-rose-50 border border-rose-100 rounded-xl">
                            Rejection reason: "{{ $seller->kyc_rejection_reason }}"
                        </p>
                    @endif
                </div>

                <!-- KYC Verification forms -->
                @if($seller->kyc_status === 'pending')
                    <div class="space-y-4 pt-4 border-t border-border" x-data="{ rejecting: false }">
                        <form action="{{ route('admin.sellers.kyc', $seller->id) }}" method="POST" x-show="!rejecting">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit"
                                class="w-full bg-primary text-primary-foreground hover:bg-primary/90  font-semibold py-3 px-4 rounded-lg ring-0 border border-black/10 transition-all flex items-center justify-center space-x-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Approve Documents</span>
                            </button>
                        </form>

                        <button @click="rejecting = true" x-show="!rejecting"
                            class="w-full bg-muted hover:bg-rose-50 text-foreground hover:text-rose-700 font-semibold py-3 px-4 rounded-xl transition-all border border-border hover:border-rose-200 flex items-center justify-center space-x-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Reject Documents</span>
                        </button>

                        <form action="{{ route('admin.sellers.kyc', $seller->id) }}" method="POST" x-show="rejecting"
                            class="space-y-4 bg-muted p-4 border border-border rounded-xl">
                            @csrf
                            <input type="hidden" name="status" value="rejected">

                            <div>
                                <label for="reason" class="block text-xs font-semibold text-muted-foreground mb-2 uppercase">Reason for
                                    Rejection</label>
                                <textarea id="reason" name="reason" rows="3" required
                                    placeholder="Specify why documents are invalid..."
                                    class="w-full p-3 border border-border rounded-xl text-sm focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card"></textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="rejecting = false"
                                    class="flex-1 bg-card hover:bg-muted text-foreground text-xs font-bold py-2 px-3 border border-border rounded-lg transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="flex-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm transition-all">
                                    Submit Rejection
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-4 bg-muted border border-border rounded-xl text-center text-xs text-muted-foreground">
                        Decision processed on {{ $seller->updated_at->format('M d, Y H:i') }}.
                    </div>
                @endif
            </div>

            <!-- Administrative Controls Card -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                <h3 class="font-bold text-foreground text-lg flex items-center">
                    <i data-lucide="shield" class="w-5 h-5 mr-2 text-muted-foreground"></i>
                    Administrative Overrides
                </h3>

                <!-- Account Status Control -->
                <div class="space-y-3 pt-2 border-t border-border">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Account Status</span>
                        <span class="px-2.5 py-1 text-[10px] rounded-full font-bold inline-block
                            @if($seller->status === 'active') bg-emerald-50 text-emerald-705 border border-emerald-200/60
                            @elseif($seller->status === 'suspended') bg-amber-100 text-amber-800
                            @elseif($seller->status === 'banned') bg-rose-50 text-rose-705 border border-rose-200/60
                            @else bg-muted text-foreground @endif">
                            {{ ucfirst($seller->status) }}
                        </span>
                    </div>

                    <form action="{{ route('admin.sellers.status-update', $seller->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <select name="status"
                            class="flex-1 p-2 border border-border rounded-xl bg-muted text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:bg-card transition-all">
                            <option value="active" {{ $seller->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ $seller->status === 'suspended' ? 'selected' : '' }}>Suspended
                            </option>
                            <option value="banned" {{ $seller->status === 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                        <button type="submit"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 font-bold px-3 py-2 rounded-xl text-xs shadow-sm transition-all active:scale-[0.98]">
                            Apply
                        </button>
                    </form>
                </div>

                <!-- Verification Badge Control -->
                <div class="space-y-3 pt-4 border-t border-border">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Verification Badge</span>
                        <span class="inline-flex items-center text-sm font-extrabold"
                            style="color: {{ $seller->badge_color }};">
                            @if($seller->badge_level === 'basic')
                                <img src="{{ asset('website_assets/images/basic.png') }}" class="w-6 h-6 mr-1.5 shrink-0"
                                    alt="Basic Seller">
                            @elseif($seller->badge_level === 'verified')
                                <img src="{{ asset('website_assets/images/verified.png') }}" class="w-6 h-6 mr-1.5 shrink-0"
                                    alt="Verified Seller">
                            @elseif($seller->badge_level === 'fulfilled')
                                <img src="{{ asset('website_assets/images/FULFILLED.png') }}" class="w-6 h-6 mr-1.5 shrink-0"
                                    alt="Fulfilled Seller">
                            @else
                                <i data-lucide="{{ $seller->badge_icon }}" class="w-6 h-6 mr-1.5 shrink-0"></i>
                            @endif
                            {{ $seller->badge_label }}
                        </span>
                    </div>

                    <form action="{{ route('admin.sellers.badge-update', $seller->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <select name="badge_level"
                            class="flex-1 p-2 border border-border rounded-xl bg-muted text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:bg-card transition-all">
                            <option value="basic" {{ $seller->badge_level === 'basic' ? 'selected' : '' }}>Basic</option>
                            <option value="verified" {{ $seller->badge_level === 'verified' ? 'selected' : '' }}>Verified
                            </option>
                            <option value="fulfilled" {{ $seller->badge_level === 'fulfilled' ? 'selected' : '' }}>Fulfilled
                            </option>
                        </select>
                        <button type="submit"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 font-bold px-3 py-2 rounded-xl text-xs shadow-sm transition-all active:scale-[0.98]">
                            Apply
                        </button>
                    </form>
                </div>
            </div>

            <!-- Shop Visit Verification Card -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-foreground text-lg mb-4 flex items-center">
                    <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-muted-foreground"></i>
                    Shop Visit Verification
                </h3>

                <div>
                    @if($seller->shop_visit_verified)
                        <span
                            class="px-3 py-1.5 text-xs rounded-full font-semibold inline-block bg-emerald-50 text-emerald-705 border border-emerald-200/60">
                            Status: Shop Visit Verified
                        </span>
                        <p class="text-xs text-muted-foreground mt-3 font-semibold">
                            Verified on: {{ $seller->shop_visit_date ? $seller->shop_visit_date->format('M d, Y H:i') : 'N/A' }}
                        </p>
                    @else
                        <span class="px-3 py-1.5 text-xs rounded-full font-semibold inline-block bg-amber-50 text-amber-705 border border-amber-200/60">
                            Status: Pending Visit Verification
                        </span>

                        <form action="{{ route('admin.sellers.visit-verify', $seller->id) }}" method="POST"
                            class="mt-4 pt-4 border-t border-border">
                            @csrf
                            <button type="submit"
                                class="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-semibold py-3 px-4 rounded-lg ring-0 transition-all flex items-center justify-center space-x-2">
                                <i data-lucide="map-pin" class="w-4 h-4 text-muted-foreground"></i>
                                <span>Mark Shop Visit Verified</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Metrics Stats -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-foreground text-lg">Performance Metrics</h3>
                    <a href="{{ route('admin.ratings', ['seller_id' => $seller->id]) }}"
                        class="text-xs text-yellow-650 hover:text-foreground hover:underline font-bold flex items-center gap-1 transition-all">
                        <span>Inspect Ratings</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="space-y-4 text-sm">
                    <a href="{{ route('admin.ratings', ['seller_id' => $seller->id]) }}"
                        class="flex justify-between items-center py-2 border-b border-border hover:text-muted-foreground group transition-all block">
                        <span class="text-muted-foreground group-hover:text-muted-foreground">Star Rating</span>
                        <span class="font-bold text-foreground flex items-center group-hover:text-muted-foreground">
                            <i data-lucide="star" class="w-4 h-4 text-yellow-500 fill-current mr-1"></i>
                            {{ $seller->metrics->star_rating ?? '0.00' }} / 5.0
                        </span>
                    </a>

                    <!-- Sub-ratings breakdown -->
                    <div class="pl-4 space-y-2 border-b border-border pb-3 text-xs">
                        <div class="flex justify-between items-center text-muted-foreground">
                            <span>Item Accuracy</span>
                            <span class="font-semibold text-foreground">
                                {{ $subRatings && $subRatings->item_accuracy ? number_format($subRatings->item_accuracy, 2) : '0.00' }}
                                / 5.0
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-muted-foreground">
                            <span>Packaging Quality</span>
                            <span class="font-semibold text-foreground">
                                {{ $subRatings && $subRatings->packaging ? number_format($subRatings->packaging, 2) : '0.00' }}
                                / 5.0
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-muted-foreground">
                            <span>Shipping Speed</span>
                            <span class="font-semibold text-foreground">
                                {{ $subRatings && $subRatings->shipping_speed ? number_format($subRatings->shipping_speed, 2) : '0.00' }}
                                / 5.0
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-muted-foreground">
                            <span>Communication</span>
                            <span class="font-semibold text-foreground">
                                {{ $subRatings && $subRatings->communication ? number_format($subRatings->communication, 2) : '0.00' }}
                                / 5.0
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-border">
                        <span class="text-muted-foreground">Cooperation Score</span>
                        <span class="font-bold text-foreground">
                            {{ $seller->metrics->cooperation_score ?? '10.00' }} / 10
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-muted-foreground">Total Sales Orders</span>
                        <span class="font-bold text-[#111827] bg-primary text-primary-foreground hover:bg-primary/90/30 px-2 py-0.5 rounded-full text-xs">
                            {{ $seller->metrics->total_orders ?? '0' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection