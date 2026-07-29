@extends('layouts.admin')

@section('title', 'Resolve Dispute')
@section('page_title', 'Dispute Resolution Board')

@section('content')
 <div class="mb-6">
 <a href="{{ route('admin.disputes') }}"
 class="inline-flex items-center space-x-1.5 text-sm font-semibold text-muted-foreground">
 <i data-lucide="arrow-left" class="w-4 h-4"></i>
 <span>Back to Disputes list</span>
 </a>
 </div>

 <!-- Main Workspace Grid -->
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

 <!-- Left / Center: Conversation Workspace -->
 <div class="lg:col-span-2 flex flex-col space-y-6">

 <!-- Dispute Info Header Card -->
 <div class="rounded-xl p-6 bg-card text-card-foreground border border-border">
 <div class="flex items-center justify-between mb-2">
 <h2 class="text-xl font-bold text-foreground">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}
 </h2>
 <div class="flex items-center gap-2">
 @if($dispute->status === 'resolved')
 <form action="{{ route('admin.disputes.reopen', $dispute->id) }}" method="POST">
 @csrf
 <button type="submit"
 class="inline-flex items-center gap-2 font-semibold px-4 py-2 rounded-lg text-sm transition-all bg-primary text-primary-foreground">
 <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
 <span>Reopen Dispute</span>
 </button>
 </form>
 @endif
 <button type="button" onclick="openResolutionDrawer()"
 class="inline-flex items-center gap-2 font-semibold px-4 py-2 rounded-lg text-sm transition-all bg-primary text-primary-foreground">
 <i data-lucide="panel-right-open" class="w-4 h-4"></i>
 <span>{{ $dispute->status === 'resolved' ? 'View Resolution Details' : 'Open Resolve Panel' }}</span>
 </button>
 </div>
 </div>
 <p class="text-xs text-muted-foreground">Order: <span
 class="font-bold text-foreground">#{{ $dispute->order->order_number }}</span> | Raised:
 {{ $dispute->created_at->format('d M Y H:i') }} | Status:
 <span class="px-2 py-0.5 text-xs rounded-full font-semibold border inline-block
 @if($dispute->status === 'open') bg-muted text-foreground border-border/60
 @elseif($dispute->status === 'seller_responded') bg-muted text-foreground border-border/60
 @elseif($dispute->status === 'under_review') bg-muted text-foreground border-border
 @elseif($dispute->status === 'resolved') bg-muted text-foreground border-border/60
 @else bg-muted border-border text-foreground @endif">
 {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
 </span>
 </p>

 <!-- Buyer Complaint Section -->
 <div class="mt-6 p-4 bg-muted border border-border/60 rounded-xl">
 <span class="text-xs font-semibold text-foreground block uppercase mb-2">Buyer Complaint</span>
 <p class="text-sm whitespace-pre-wrap text-foreground">{{ $dispute->description }}</p>
 </div>

 <!-- Buyer Evidence Images (Lightbox) -->
 @if($dispute->evidence_images && count($dispute->evidence_images) > 0)
 <div class="mt-6">
 <span class="text-xs font-semibold block uppercase mb-3 flex items-center gap-2 text-muted-foreground">
 <i data-lucide="image" class="w-3.5 h-3.5"></i>
 Buyer Evidence ({{ count($dispute->evidence_images) }} images)
 </span>
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
 @foreach($dispute->evidence_images as $idx => $url)
 <button type="button" onclick="openLightbox('{{ $url }}')"
 class="group block aspect-video rounded-lg overflow-hidden hover:shadow-md transition-all cursor-pointer relative border border-border bg-muted">
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
 <div class="rounded-xl p-6 bg-card text-card-foreground border border-border">
 <h3 class="font-bold text-sm mb-4 flex items-center gap-2 text-foreground">
 <i data-lucide="message-circle" class="w-4 h-4"></i>
 Seller Response & Counter Evidence
 </h3>

 <div class="space-y-4 mb-4 p-4 bg-muted border border-border/60 rounded-xl">
 @foreach($dispute->responses->where('user_id', $dispute->seller_id) as $response)
 <div>
 <p class="text-xs font-semibold text-foreground mb-2">{{ $dispute->seller->shop_name }} •
 {{ $response->created_at->diffForHumans() }}</p>
 <p class="text-sm whitespace-pre-wrap text-foreground">{{ $response->response_text }}</p>

 @if($response->evidence_images && count($response->evidence_images) > 0)
 <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
 @foreach($response->evidence_images as $url)
 <button type="button" onclick="openLightbox('{{ $url }}')"
 class="group block aspect-video rounded-lg overflow-hidden border border-border bg-muted/60 hover:border-ring transition-all cursor-pointer">
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
 <div class="rounded-lg flex-1 flex flex-col overflow-hidden bg-card text-card-foreground border border-border">
 <div class="px-6 py-4 flex items-center justify-between border-b border-border bg-muted">
 <h3 class="font-bold text-sm text-foreground">Admin Chat Log</h3>
 <span class="px-2 py-0.5 text-[10px] rounded font-semibold bg-primary text-primary-foreground">SECURED
 CHAT</span>
 </div>

 <!-- Conversation Box -->
 <div class="p-6 space-y-6 overflow-y-auto max-h-[400px]">
 @if($dispute->status === 'resolved')
 <div
 class="p-4 bg-muted border border-border text-foreground/60 rounded-xl text-sm font-semibold mb-6 flex items-start gap-3 ">
 <i data-lucide="check-circle" class="w-5 h-5 text-muted-foreground shrink-0 mt-0.5"></i>
 <div>
 <p class="font-bold text-foreground">Dispute Resolved by Admin</p>
 <p class="text-xs text-foreground mt-1">Decision: <span
 class="font-bold uppercase">{{ str_replace('_', ' ', $dispute->admin_decision) }}</span>
 </p>
 <p class="text-xs text-foreground">Fault Assigned: <span
 class="font-bold uppercase">{{ $dispute->fault_assigned_to }}</span></p>
 @if($dispute->buyer_payout > 0 || $dispute->seller_payout > 0)
 <p class="text-xs text-foreground">Payouts: Buyer:
 ₹{{ number_format($dispute->buyer_payout, 2) }} | Seller:
 ₹{{ number_format($dispute->seller_payout, 2) }}</p>
 @endif
 @if($dispute->resolution_notes)
 <p
 class="text-xs text-foreground mt-2 bg-card/60 p-2.5 rounded-xl border border-border italic">
 "{{ $dispute->resolution_notes }}"</p>
 @endif
 </div>
 </div>
 @endif

 @forelse($dispute->responses->where('user_id', '!=', $dispute->seller_id)->where('user_id', '!=', $dispute->buyer_id) as $resp)
 <div class="flex items-start space-x-3.5 justify-end">
 <div class="max-w-[80%] rounded-xl p-4 border bg-primary border-primary text-primary-foreground">
 <div class="flex items-center justify-between gap-6 mb-1">
 <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">
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
 <p class="text-center text-sm py-8 text-muted-foreground">No admin messages yet.</p>
 @endforelse
 </div>

 <!-- Send Response Box (Only active for open/reviewed disputes) -->
 @if($dispute->status !== 'resolved')
 <div class="p-4 border-t border-border bg-muted">
 <form action="{{ route('admin.disputes.response', $dispute->id) }}" method="POST" class="flex gap-3">
 @csrf
 <input type="text" name="response_text" required placeholder="Type a message as moderator..."
 class="w-full px-4 py-2 rounded-lg text-xs focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none h-9 border border-input bg-card text-foreground">

 <button type="submit"
 class="font-semibold px-4 rounded-lg text-xs transition-colors shrink-0 h-9 bg-primary text-primary-foreground">
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
 <div class="rounded-xl p-6 bg-card text-card-foreground border border-border">
 <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-foreground">
 <i data-lucide="shopping-cart" class="w-4 h-4"></i>
 Order Details
 </h3>
 <div class="space-y-3.5 text-xs">
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Order Number</span>
 <span class="font-bold text-foreground">#{{ $dispute->order->order_number }}</span>
 </div>
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Order Amount</span>
 <span class="font-bold text-foreground">₹{{ number_format($dispute->order->total_amount, 2) }}</span>
 </div>
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Product Price</span>
 <span class="font-medium font-semibold text-foreground">₹{{ number_format($dispute->order->product_amount, 2) }}</span>
 </div>
 <div class="flex justify-between items-center py-1.5">
 <span class="text-muted-foreground">Escrow Held</span>
 <span class="font-bold text-foreground bg-muted border border-border/60 px-2 py-0.5 rounded-full text-[10px]">
 ₹{{ number_format($dispute->order->escrow->amount_held ?? $dispute->order->total_amount, 2) }}
 </span>
 </div>
 </div>
 </div>

 <!-- Parties Card -->
 <div class="rounded-xl p-6 bg-card text-card-foreground border border-border">
 <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-foreground">
 <i data-lucide="users" class="w-4 h-4"></i>
 Parties Involved
 </h3>
 <div class="space-y-3">
 <div class="p-3 bg-muted border border-border/60 rounded-lg">
 <p class="text-[10px] font-semibold text-foreground mb-1">Buyer</p>
 <p class="text-xs font-semibold text-foreground">{{ $dispute->buyer->name }}</p>
 </div>
 <div class="p-3 bg-muted border border-border/60 rounded-lg">
 <p class="text-[10px] font-semibold text-foreground mb-1">Seller</p>
 <p class="text-xs font-semibold text-foreground">{{ $dispute->seller->shop_name }}</p>
 </div>
 </div>
 </div>

 <!-- Status Timeline -->
 <div class="rounded-xl p-6 bg-card text-card-foreground border border-border">
 <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-foreground">
 <i data-lucide="clock" class="w-4 h-4"></i>
 Timeline
 </h3>
 <div class="space-y-2.5 text-xs">
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Raised</span>
 <span class="font-medium text-foreground">{{ $dispute->created_at->format('d M Y H:i') }}</span>
 </div>
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Time Elapsed</span>
 <span class="font-medium text-foreground">{{ $dispute->time_since_raised }}</span>
 </div>
 @if($dispute->resolved_at)
 <div class="flex justify-between items-center py-1.5">
 <span class="text-muted-foreground">Resolved</span>
 <span class="font-medium text-foreground">{{ $dispute->resolved_at->format('d M Y H:i') }}</span>
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
 <div class="w-full max-w-2xl shadow-2xl flex flex-col overflow-hidden bg-card text-card-foreground">

 <!-- Drawer Header -->
 <div class="flex items-center justify-between px-6 py-4 border-b border-border bg-muted">
 <h2 class="text-lg font-bold text-foreground">Resolve Dispute</h2>
 <button type="button" onclick="closeResolutionDrawer()"
 class="p-2 rounded-lg transition-all text-muted-foreground">
 <i data-lucide="x" class="w-5 h-5"></i>
 </button>
 </div>

 <!-- Drawer Content -->
 <div class="flex-1 overflow-y-auto">

 @if($dispute->status === 'resolved')
 <!-- Already Resolved Message -->
 <div class="p-6">
 <div
 class="p-4 bg-muted border border-border text-foreground/60 rounded-xl text-sm font-semibold">
 <p class="font-bold text-foreground mb-2 flex items-center gap-2">
 <i data-lucide="check-circle" class="w-5 h-5"></i>
 This dispute has already been resolved
 </p>
 <p class="text-foreground mb-3">Decision: <span
 class="font-bold">{{ ucfirst(str_replace('_', ' ', $dispute->admin_decision)) }}</span></p>
 <p class="text-foreground mb-3">Fault: <span
 class="font-bold">{{ ucfirst($dispute->fault_assigned_to) }}</span></p>
 @if($dispute->resolution_notes)
 <p class="text-foreground mt-3 p-2 bg-card/50 rounded border border-border">
 {{ $dispute->resolution_notes }}</p>
 @endif
 <form action="{{ route('admin.disputes.reopen', $dispute->id) }}" method="POST"
 class="mt-4 pt-4 border-t border-border">
 @csrf
 <button type="submit"
 class="w-full font-semibold py-2.5 px-4 rounded-lg text-xs shadow transition-all flex items-center justify-center space-x-2 bg-primary text-primary-foreground">
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
 <label for="decision" class="block text-[10px] font-semibold mb-1.5 uppercase tracking-wide text-muted-foreground">Resolution Decision</label>
 <select id="decision" name="decision" required onchange="updatePayoutPreview()"
 class="w-full p-2.5 rounded-lg text-xs focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none h-9 border border-input bg-card text-foreground">
 <option value="">Select a decision...</option>
 <option value="seller_favor">✓ Seller Favor (Full payout to seller)</option>
 <option value="buyer_favor">✓ Buyer Favor (Full refund to buyer)</option>
 <option value="partial">⟿ Partial Resolution (Split payout)</option>
 </select>
 </div>

 <!-- Refund Amount (only for partial) -->
 <div id="refundAmountField" class="hidden">
 <label for="refund_amount" class="block text-[10px] font-semibold mb-1.5 uppercase tracking-wide text-muted-foreground">Buyer Refund Amount (₹)</label>
 <div class="flex gap-3">
 <input type="number" id="refund_amount" name="refund_amount" step="0.01" min="0"
 max="{{ $dispute->order->escrow->amount_held ?? $dispute->order->total_amount }}"
 placeholder="0.00" onchange="updatePayoutPreview()"
 class="flex-1 p-2.5 rounded-lg text-xs focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none h-9 border border-input bg-card text-foreground">
 <div
 class="flex items-center px-3 rounded-lg text-xs font-semibold h-9 bg-muted border border-border text-muted-foreground">
 Max:
 ₹{{ number_format($dispute->order->escrow->amount_held ?? $dispute->order->total_amount, 2) }}
 </div>
 </div>
 </div>

 <!-- Fault Assignment -->
 <div>
 <label for="fault" class="block text-[10px] font-semibold mb-1.5 uppercase tracking-wide text-muted-foreground">Fault Assigned To</label>
 <select id="fault" name="fault" required
 class="w-full p-2.5 rounded-lg text-xs focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none h-9 border border-input bg-card text-foreground">
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
 class="block text-[10px] font-semibold mb-1.5 uppercase tracking-wide text-muted-foreground">Investigation Notes & Findings</label>
 <textarea id="admin_notes" name="admin_notes" rows="5" required
 placeholder="Document your investigation findings, evidence reviewed, and reasoning for this decision..."
 class="w-full p-2.5 rounded-lg text-xs focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none border border-input bg-card text-foreground"></textarea>
 </div>

 <!-- Payout Preview -->
 <div id="payoutPreview" class="p-4 rounded-lg space-y-2 hidden bg-muted border border-border">
 <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Payout Preview</p>
 <div class="grid grid-cols-2 gap-4">
 <div class="p-3 rounded-lg border border-border bg-card">
 <p class="text-[10px] font-semibold text-muted-foreground">Refund to Buyer</p>
 <p id="buyerPayoutPreview" class="text-base font-bold text-rose-600">₹0.00</p>
 </div>
 <div class="p-3 rounded-lg border border-border bg-card">
 <p class="text-[10px] font-semibold text-muted-foreground">Payout to Seller</p>
 <p id="sellerPayoutPreview" class="text-base font-bold text-muted-foreground">₹0.00</p>
 </div>
 </div>
 </div>

 <!-- Submit Button -->
 <button type="submit"
 class="w-full font-semibold py-2.5 px-4 rounded-lg transition-all flex items-center justify-center space-x-2 text-xs h-10 bg-primary text-primary-foreground">
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