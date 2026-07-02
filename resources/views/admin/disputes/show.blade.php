@extends('layouts.admin')

@section('title', 'Resolve Dispute')
@section('page_title', 'Dispute Resolution Board')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.disputes') }}"
            class="inline-flex items-center space-x-1.5 text-sm font-semibold text-zinc-500 hover:text-zinc-800">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Disputes list</span>
        </a>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left / Center: Conversation Workspace -->
        <div class="lg:col-span-2 flex flex-col space-y-6">

            <!-- Dispute Info Header Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-bold text-zinc-900">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}
                    </h2>
                    <div class="flex items-center gap-2">
                        @if($dispute->status === 'resolved')
                            <form action="{{ route('admin.disputes.reopen', $dispute->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 bg-zinc-900 hover:bg-zinc-800 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-all shadow-sm">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    <span>Reopen Dispute</span>
                                </button>
                            </form>
                        @endif
                        <button type="button" onclick="openResolutionDrawer()"
                            class="inline-flex items-center gap-2 bg-zinc-900 hover:bg-zinc-800 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-all">
                            <i data-lucide="panel-right-open" class="w-4 h-4"></i>
                            <span>{{ $dispute->status === 'resolved' ? 'View Resolution Details' : 'Open Resolve Panel' }}</span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-zinc-500">Order: <span
                        class="font-bold text-zinc-800">#{{ $dispute->order->order_number }}</span> | Raised:
                    {{ $dispute->created_at->format('d M Y H:i') }} | Status:
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold border inline-block
                        @if($dispute->status === 'open') bg-amber-50 text-amber-705 border-amber-200/60
                        @elseif($dispute->status === 'seller_responded') bg-blue-50 text-blue-700 border-blue-200/60
                        @elseif($dispute->status === 'under_review') bg-zinc-50 text-zinc-700 border-zinc-200
                        @elseif($dispute->status === 'resolved') bg-emerald-50 text-emerald-705 border-emerald-200/60
                        @else bg-zinc-50 border-zinc-200 text-zinc-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                    </span>
                </p>

                <!-- Buyer Complaint Section -->
                <div class="mt-6 p-4 bg-rose-50/50 border border-rose-100 rounded-xl">
                    <span class="text-xs font-semibold text-rose-800 block uppercase mb-2">Buyer Complaint</span>
                    <p class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $dispute->description }}</p>
                </div>

                <!-- Buyer Evidence Images (Lightbox) -->
                @if($dispute->evidence_images && count($dispute->evidence_images) > 0)
                    <div class="mt-6">
                        <span class="text-xs font-semibold text-zinc-600 block uppercase mb-3 flex items-center gap-2">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            Buyer Evidence ({{ count($dispute->evidence_images) }} images)
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($dispute->evidence_images as $idx => $url)
                                <button type="button" onclick="openLightbox('{{ $url }}')"
                                    class="group block aspect-video rounded-lg overflow-hidden border border-zinc-200 bg-zinc-50 hover:border-zinc-400 hover:shadow-md transition-all cursor-pointer relative">
                                    <img src="{{ $url }}" class="w-full h-full object-cover" alt="buyer-evidence">
                                    <div
                                        class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center cursor-pointer">
                                        <i data-lucide="zoom-in"
                                            class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-all"></i>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Seller Response (if exists) -->
            @if($dispute->responses->where('user_id', $dispute->seller_id)->count() > 0)
                <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                    <h3 class="font-bold text-zinc-800 text-sm mb-4 flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        Seller Response & Counter Evidence
                    </h3>

                    <div class="space-y-4 mb-4 p-4 bg-blue-50/50 border border-blue-100 rounded-xl">
                        @foreach($dispute->responses->where('user_id', $dispute->seller_id) as $response)
                            <div>
                                <p class="text-xs font-semibold text-blue-700 mb-2">{{ $dispute->seller->shop_name }} •
                                    {{ $response->created_at->diffForHumans() }}</p>
                                <p class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $response->response_text }}</p>

                                @if($response->evidence_images && count($response->evidence_images) > 0)
                                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        @foreach($response->evidence_images as $url)
                                            <button type="button" onclick="openLightbox('{{ $url }}')"
                                                class="group block aspect-video rounded-lg overflow-hidden border border-blue-200 bg-blue-50 hover:border-zinc-400 transition-all cursor-pointer">
                                                <img src="{{ $url }}" class="w-full h-full object-cover" alt="seller-evidence">
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Chat / Response Log -->
            <div class="bg-white border border-zinc-200 rounded-lg ring-1 ring-zinc-950/5 flex-1 flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-zinc-800 text-sm">Admin Chat Log</h3>
                    <span class="px-2 py-0.5 text-[10px] bg-zinc-900 text-white rounded font-semibold">SECURED
                        CHAT</span>
                </div>

                <!-- Conversation Box -->
                <div class="p-6 space-y-6 overflow-y-auto max-h-[400px]">
                    @if($dispute->status === 'resolved')
                        <div
                            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold mb-6 flex items-start gap-3 shadow-sm">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-bold text-emerald-950">Dispute Resolved by Admin</p>
                                <p class="text-xs text-emerald-700 mt-1">Decision: <span
                                        class="font-bold uppercase">{{ str_replace('_', ' ', $dispute->admin_decision) }}</span>
                                </p>
                                <p class="text-xs text-emerald-700">Fault Assigned: <span
                                        class="font-bold uppercase">{{ $dispute->fault_assigned_to }}</span></p>
                                @if($dispute->buyer_payout > 0 || $dispute->seller_payout > 0)
                                    <p class="text-xs text-emerald-700">Payouts: Buyer:
                                        ₹{{ number_format($dispute->buyer_payout, 2) }} | Seller:
                                        ₹{{ number_format($dispute->seller_payout, 2) }}</p>
                                @endif
                                @if($dispute->resolution_notes)
                                    <p
                                        class="text-xs text-emerald-800 mt-2 bg-white/60 p-2.5 rounded-xl border border-emerald-200/50 italic">
                                        "{{ $dispute->resolution_notes }}"</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @forelse($dispute->responses->where('user_id', '!=', $dispute->seller_id)->where('user_id', '!=', $dispute->buyer_id) as $resp)
                        <div class="flex items-start space-x-3.5 justify-end">
                            <div class="max-w-[80%] rounded-xl p-4 border bg-zinc-900 border-zinc-800 text-white">
                                <div class="flex items-center justify-between gap-6 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-300">
                                        {{ $resp->user->name }} (Admin)
                                    </span>
                                    <span class="text-[9px] opacity-60">{{ $resp->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs leading-relaxed whitespace-pre-wrap">{{ $resp->response_text }}</p>
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($resp->user->name) }}&background=000&color=fff"
                                class="w-8 h-8 rounded-lg" alt="avatar">
                        </div>
                    @empty
                        <p class="text-center text-zinc-400 text-sm py-8">No admin messages yet.</p>
                    @endforelse
                </div>

                <!-- Send Response Box (Only active for open/reviewed disputes) -->
                @if($dispute->status !== 'resolved')
                    <div class="p-4 border-t border-zinc-200 bg-zinc-50">
                        <form action="{{ route('admin.disputes.response', $dispute->id) }}" method="POST" class="flex gap-3">
                            @csrf
                            <input type="text" name="response_text" required placeholder="Type a message as moderator..."
                                class="w-full px-4 py-2 border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none bg-white h-9">

                            <button type="submit"
                                class="bg-zinc-900 hover:bg-zinc-800 text-white font-semibold px-4 rounded-lg text-xs transition-colors shrink-0 h-9">
                                <span>Send</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Summary Cards -->
        <div class="space-y-6">

            <!-- Order Details Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4 flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    Order Details
                </h3>
                <div class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Order Number</span>
                        <span class="font-bold text-zinc-900">#{{ $dispute->order->order_number }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Order Amount</span>
                        <span class="font-bold text-zinc-900">₹{{ number_format($dispute->order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Product Price</span>
                        <span
                            class="font-medium text-zinc-800 font-semibold">₹{{ number_format($dispute->order->product_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-zinc-500">Escrow Held</span>
                        <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full text-[10px] border border-amber-200">
                            ₹{{ number_format($dispute->order->escrow->amount_held ?? $dispute->order->total_amount, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Parties Card -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Parties Involved
                </h3>
                <div class="space-y-3">
                    <div class="p-3 bg-rose-50 border border-rose-100 rounded-lg">
                        <p class="text-[10px] font-semibold text-rose-700 mb-1">Buyer</p>
                        <p class="text-xs font-semibold text-zinc-900">{{ $dispute->buyer->name }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                        <p class="text-[10px] font-semibold text-blue-700 mb-1">Seller</p>
                        <p class="text-xs font-semibold text-zinc-900">{{ $dispute->seller->shop_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-zinc-800 text-lg mb-4 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    Timeline
                </h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Raised</span>
                        <span class="font-medium text-zinc-800">{{ $dispute->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-zinc-100">
                        <span class="text-zinc-500">Time Elapsed</span>
                        <span class="font-medium text-zinc-800">{{ $dispute->time_since_raised }}</span>
                    </div>
                    @if($dispute->resolved_at)
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-zinc-500">Resolved</span>
                            <span class="font-medium text-zinc-800">{{ $dispute->resolved_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- ─────────────────────────────────────────────────────────────────────── -->
    <!-- RESOLUTION DRAWER MODAL ─────────────────────────────────────────────── -->
    <!-- ─────────────────────────────────────────────────────────────────────── -->

    <div id="resolutionDrawer" class="hidden fixed inset-0 z-50 flex">
        <!-- Overlay -->
        <div class="flex-1 bg-black/40 cursor-pointer" onclick="closeResolutionDrawer()"></div>

        <!-- Drawer Panel -->
        <div class="w-full max-w-2xl bg-white shadow-2xl flex flex-col overflow-hidden">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 bg-zinc-50/50">
                <h2 class="text-lg font-bold text-zinc-900">Resolve Dispute</h2>
                <button type="button" onclick="closeResolutionDrawer()"
                    class="text-zinc-400 hover:text-zinc-650 p-2 hover:bg-zinc-100 rounded-lg transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Drawer Content -->
            <div class="flex-1 overflow-y-auto">

                @if($dispute->status === 'resolved')
                    <!-- Already Resolved Message -->
                    <div class="p-6">
                        <div
                            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold">
                            <p class="font-bold text-emerald-950 mb-2 flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                This dispute has already been resolved
                            </p>
                            <p class="text-emerald-700 mb-3">Decision: <span
                                    class="font-bold">{{ ucfirst(str_replace('_', ' ', $dispute->admin_decision)) }}</span></p>
                            <p class="text-emerald-700 mb-3">Fault: <span
                                    class="font-bold">{{ ucfirst($dispute->fault_assigned_to) }}</span></p>
                            @if($dispute->resolution_notes)
                                <p class="text-emerald-700 mt-3 p-2 bg-white/50 rounded border border-emerald-200">
                                    {{ $dispute->resolution_notes }}</p>
                            @endif
                            <form action="{{ route('admin.disputes.reopen', $dispute->id) }}" method="POST"
                                class="mt-4 pt-4 border-t border-emerald-200/50">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold py-2.5 px-4 rounded-lg text-xs shadow transition-all flex items-center justify-center space-x-2">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    <span>Reopen Dispute for Reinvestigation</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Resolution Form -->
                    <form action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST" class="p-6 space-y-6">
                        @csrf

                        <!-- Decision Field -->
                        <div>
                            <label for="decision" class="block text-[10px] font-semibold text-zinc-500 mb-1.5 uppercase tracking-wide">Resolution Decision</label>
                            <select id="decision" name="decision" required onchange="updatePayoutPreview()"
                                class="w-full p-2.5 border border-zinc-200 bg-white rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none h-9">
                                <option value="">Select a decision...</option>
                                <option value="seller_favor">✓ Seller Favor (Full payout to seller)</option>
                                <option value="buyer_favor">✓ Buyer Favor (Full refund to buyer)</option>
                                <option value="partial">⟿ Partial Resolution (Split payout)</option>
                            </select>
                        </div>

                        <!-- Refund Amount (only for partial) -->
                        <div id="refundAmountField" class="hidden">
                            <label for="refund_amount" class="block text-[10px] font-semibold text-zinc-500 mb-1.5 uppercase tracking-wide">Buyer Refund Amount (₹)</label>
                            <div class="flex gap-3">
                                <input type="number" id="refund_amount" name="refund_amount" step="0.01" min="0"
                                    max="{{ $dispute->order->escrow->amount_held ?? $dispute->order->total_amount }}"
                                    placeholder="0.00" onchange="updatePayoutPreview()"
                                    class="flex-1 p-2.5 border border-zinc-200 bg-white rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none h-9">
                                <div
                                    class="flex items-center px-3 bg-zinc-50 border border-zinc-200 rounded-lg text-xs font-semibold text-zinc-650 h-9">
                                    Max:
                                    ₹{{ number_format($dispute->order->escrow->amount_held ?? $dispute->order->total_amount, 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Fault Assignment -->
                        <div>
                            <label for="fault" class="block text-[10px] font-semibold text-zinc-500 mb-1.5 uppercase tracking-wide">Fault Assigned To</label>
                            <select id="fault" name="fault" required
                                class="w-full p-2.5 border border-zinc-200 bg-white rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none h-9">
                                <option value="">Select fault assignment...</option>
                                <option value="seller">Seller - Seller is at fault</option>
                                <option value="buyer">Buyer - Buyer is at fault</option>
                                <option value="shipping">Shipping Partner - Logistics issue</option>
                                <option value="none">None - Both parties at fault / Product issue</option>
                            </select>
                        </div>

                        <!-- Admin Notes -->
                        <div>
                            <label for="admin_notes"
                                class="block text-[10px] font-semibold text-zinc-500 mb-1.5 uppercase tracking-wide">Investigation Notes & Findings</label>
                            <textarea id="admin_notes" name="admin_notes" rows="5" required
                                placeholder="Document your investigation findings, evidence reviewed, and reasoning for this decision..."
                                class="w-full p-2.5 border border-zinc-200 bg-white rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none"></textarea>
                        </div>

                        <!-- Payout Preview -->
                        <div id="payoutPreview" class="p-4 bg-zinc-50 border border-zinc-250/50 rounded-lg space-y-2 hidden">
                            <p class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wide">Payout Preview</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-lg border border-red-200">
                                    <p class="text-[10px] text-zinc-500 font-semibold">Refund to Buyer</p>
                                    <p id="buyerPayoutPreview" class="text-base font-bold text-red-600">₹0.00</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-green-200">
                                    <p class="text-[10px] text-zinc-500 font-semibold">Payout to Seller</p>
                                    <p id="sellerPayoutPreview" class="text-base font-bold text-green-600">₹0.00</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center space-x-2 text-xs h-10">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Submit Resolution</span>
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

    <!-- Lightbox Modal for Images -->
    <div id="lightboxModal" class="hidden fixed inset-0 z-[60] bg-black/80 flex items-center justify-center p-4"
        onclick="closeLightbox(event)">
        <div class="relative max-w-4xl w-full" onclick="event.stopPropagation()">
            <button type="button" onclick="closeLightbox()"
                class="absolute -top-10 right-0 text-white hover:text-gray-300 p-2">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
            <img id="lightboxImage" src="" alt="evidence" class="w-full h-auto rounded-lg max-h-[90vh] object-contain">
        </div>
    </div>

    <script>
        function openResolutionDrawer() {
            document.getElementById('resolutionDrawer').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeResolutionDrawer() {
            document.getElementById('resolutionDrawer').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openLightbox(imageUrl) {
            document.getElementById('lightboxImage').src = imageUrl;
            document.getElementById('lightboxModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox(event) {
            if (event && event.target.id !== 'lightboxModal') return;
            document.getElementById('lightboxModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function updatePayoutPreview() {
            const decision = document.getElementById('decision').value;
            const escrowAmount = {{ $dispute->order->escrow->amount_held ?? $dispute->order->total_amount }};
            const preview = document.getElementById('payoutPreview');
            let buyerPayout = 0;
            let sellerPayout = 0;

            if (decision === 'buyer_favor') {
                buyerPayout = escrowAmount;
                sellerPayout = 0;
            } else if (decision === 'seller_favor') {
                buyerPayout = 0;
                sellerPayout = escrowAmount;
            } else if (decision === 'partial') {
                document.getElementById('refundAmountField').classList.remove('hidden');
                const refundAmount = parseFloat(document.getElementById('refund_amount').value) || 0;
                buyerPayout = refundAmount;
                sellerPayout = escrowAmount - refundAmount;
            } else {
                document.getElementById('refundAmountField').classList.add('hidden');
                preview.classList.add('hidden');
                return;
            }

            document.getElementById('buyerPayoutPreview').textContent = '₹' + buyerPayout.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('sellerPayoutPreview').textContent = '₹' + sellerPayout.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            preview.classList.remove('hidden');
        }

        // Close drawer when pressing Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeResolutionDrawer();
                closeLightbox();
            }
        });
    </script>
@endsection