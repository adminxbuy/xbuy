@extends('layouts.admin')

@section('title', 'Order Details')
@section('page_title', 'Order details tracker')

@section('content')
 <div class="mb-6">
 <a href="{{ route('admin.orders') }}"
 class="inline-flex items-center space-x-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground">
 <i data-lucide="arrow-left" class="size-4"></i>
 <span>Back to Registry</span>
 </a>
 </div>

 <!-- Grid Layout -->
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

 <!-- Left side: Order tracking, Product, Address -->
 <div class="lg:col-span-2 space-y-6">

 <!-- Track Panel -->
 <div class="bg-card border border-border rounded-xl p-6 ">
 <div
 class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-border gap-4">
 <div>
 <h2 class="text-xl font-bold text-foreground">Order #{{ $order->order_number }}</h2>
 <p class="text-xs text-muted-foreground mt-1">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
 </div>
 <div>
 <span class="px-4 py-2 rounded-xl text-sm font-bold "
 style="background-color: {{ $order->status_color }}20; color: {{ $order->status_color }};">
 {{ $order->status_label }}
 </span>
 </div>
 </div>

 <!-- Tracking Timeline -->
 <div class="pt-8 px-2 max-w-xl">
 <div class="relative pl-8 space-y-8 border-l border-border">

 <!-- Step 1: Placed -->
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-emerald-500 rounded-full border-4 border-card flex items-center justify-center text-white ">
 <i data-lucide="check" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Order Placed & Invoice Generated</h4>
 <p class="text-xs text-muted-foreground mt-1">{{ $order->created_at->format('M d, Y H:i') }}</p>
 </div>

 <!-- Step 2: Payment -->
 @if($order->order_status !== 'pending_payment')
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-emerald-500 rounded-full border-4 border-card flex items-center justify-center text-white ">
 <i data-lucide="check" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Payment Secured & Escrow Locked</h4>
 <p class="text-xs text-muted-foreground mt-1">Razorpay Ref:
 {{ $order->razorpay_payment_id ?: 'captured via webhook' }}</p>
 </div>
 @else
 <div class="relative opacity-50">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-muted rounded-full border-4 border-card flex items-center justify-center text-muted-foreground"></span>
 <h4 class="text-sm font-semibold text-foreground">Escrow Payment Pending</h4>
 <p class="text-xs text-muted-foreground mt-1">Waiting for buyer payment authorization</p>
 </div>
 @endif

 <!-- Step 3: Dispatch -->
 @if(in_array($order->order_status, ['confirmed', 'label_generated', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'testing_period', 'completed']))
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-emerald-500 rounded-full border-4 border-card flex items-center justify-center text-white ">
 <i data-lucide="check" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Shipment Prepared & Dispatched</h4>
 <p class="text-xs text-muted-foreground mt-1">AWB: {{ $order->shipment->awb_number ?? 'N/A' }} |
 Provider: Shiprocket</p>
 </div>
 @else
 <div class="relative opacity-50">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-muted rounded-full border-4 border-card flex items-center justify-center text-muted-foreground"></span>
 <h4 class="text-sm font-semibold text-foreground">Shipment Confirmation Pending</h4>
 <p class="text-xs text-muted-foreground mt-1">Seller must confirm order details & generate label</p>
 </div>
 @endif

 <!-- Step 4: Delivered / Testing -->
 @if(in_array($order->order_status, ['testing_period', 'completed', 'disputed']))
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-emerald-500 rounded-full border-4 border-card flex items-center justify-center text-white ">
 <i data-lucide="check" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Delivered & Testing Period Initiated</h4>
 <p class="text-xs text-muted-foreground mt-1">Delivered on:
 {{ $order->delivered_at ? $order->delivered_at->format('M d, Y H:i') : 'N/A' }}</p>
 <p
 class="text-[11px] text-foreground font-semibold bg-muted border border-border rounded-lg p-2.5 mt-2 inline-block">
 Testing Window ends at:
 {{ $order->testing_window_ends_at ? $order->testing_window_ends_at->format('M d, Y H:i') : 'N/A' }}
 ({{ $order->testing_window_remaining_hours }} hours remaining)
 </p>
 </div>
 @else
 <div class="relative opacity-50">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-muted rounded-full border-4 border-card flex items-center justify-center text-muted-foreground"></span>
 <h4 class="text-sm font-semibold text-foreground">Delivery Verification Pending</h4>
 <p class="text-xs text-muted-foreground mt-1">Awaiting carrier transit webhook</p>
 </div>
 @endif

 <!-- Step 5: Completed -->
 @if($order->order_status === 'completed')
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-emerald-500 rounded-full border-4 border-card flex items-center justify-center text-white ">
 <i data-lucide="check" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Order Completed & Escrow Released</h4>
 <p class="text-xs text-muted-foreground mt-1">Funds transferred on:
 {{ $order->completed_at ? $order->completed_at->format('M d, Y H:i') : 'N/A' }}</p>
 </div>
 @elseif($order->order_status === 'disputed')
 <div class="relative">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-rose-600 rounded-full border-4 border-card flex items-center justify-center text-white animate-pulse">
 <i data-lucide="alert-circle" class="size-3.5"></i>
 </span>
 <h4 class="text-sm font-semibold text-foreground">Escrow Blocked / Disputed</h4>
 <p class="text-xs text-rose-600 mt-1">Dispute raised by buyer. Funds are locked indefinitely.
 </p>
 <a href="{{ route('admin.disputes.show', $order->dispute->id) }}"
 class="inline-flex items-center space-x-1 text-xs font-bold bg-muted hover:bg-primary text-primary-foreground hover:bg-primary/90 text-foreground hover:text-foreground px-2.5 py-1 rounded transition-all mt-2">
 <span>Go to dispute board</span>
 <i data-lucide="arrow-right-circle" class="w-3 h-3"></i>
 </a>
 </div>
 @else
 <div class="relative opacity-50">
 <span
 class="absolute -left-[41px] top-0.5 w-6 h-6 bg-muted rounded-full border-4 border-card flex items-center justify-center text-muted-foreground"></span>
 <h4 class="text-sm font-semibold text-foreground">Order Completion & Escrow Payout</h4>
 <p class="text-xs text-muted-foreground mt-1">Occurs automatically after testing window close or buyer
 manual release</p>
 </div>
 @endif

 </div>
 </div>
 </div>

 <!-- Product part brief -->
 <div class="bg-card border border-border rounded-xl p-6 ">
 <h3 class="font-bold text-foreground text-lg mb-4">Ordered Product Listing</h3>

 <div class="flex items-center space-x-4">
 @if($order->listing->primary_image_url)
 <img src="{{ $order->listing->primary_image_url }}"
 class="w-16 h-16 object-cover rounded-xl border border-border bg-muted" alt="product">
 @else
 <div
 class="w-16 h-16 bg-muted border border-border rounded-xl flex items-center justify-center text-muted-foreground">
 <i data-lucide="image" class="size-6"></i>
 </div>
 @endif
 <div class="flex-1 flex justify-between items-center gap-4">
 <div>
 <h4 class="font-bold text-foreground text-base hover:text-foreground transition-colors">
 <a href="{{ route('admin.listings.show', $order->listing_id) }}" target="_blank"
 class="hover:underline">
 {{ $order->listing->title }}
 </a>
 </h4>
 <p class="text-xs text-muted-foreground mt-1">Grade: <span class="font-bold text-foreground">Grade
 {{ $order->listing->grade }}</span> | Serial: <span
 class="font-mono bg-muted px-1.5 py-0.5 rounded border border-border">{{ $order->listing->serial_number }}</span>
 </p>
 </div>
 <a href="{{ route('admin.listings.show', $order->listing_id) }}" target="_blank"
 class="inline-flex items-center gap-1.5 bg-muted hover:bg-muted text-foreground hover:text-yellow-805 font-bold px-3 py-2 rounded-xl text-xs border border-border/50 transition-all shrink-0">
 <i data-lucide="eye" class="size-4"></i>
 <span>View Listing</span>
 </a>
 </div>
 </div>
 </div>

 <!-- Direct Chat System (Buyer) -->
 <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden flex flex-col h-[400px]"
 x-data="buyerChatManager()">
 <!-- Chat Header -->
 <div class="px-6 py-4 border-b border-border bg-muted flex items-center justify-between">
 <div class="flex items-center space-x-2.5">
 <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground hover:bg-primary/90/15 flex items-center justify-center text-foreground">
 <i data-lucide="message-square" class="size-4"></i>
 </div>
 <div>
 <h3 class="text-sm font-semibold text-foreground">Buyer Direct Conversation</h3>
 <p class="text-[10px] text-muted-foreground font-medium">Chat history with {{ $order->buyer->name }}</p>
 </div>
 </div>
 <button type="button" @click="fetchMessages()"
 class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none"
 title="Refresh Chat">
 <i data-lucide="refresh-cw" class="size-4"></i>
 </button>
 </div>

 <!-- Messages Box -->
 <div id="buyer-chat-messages-box" class="flex-1 overflow-y-auto p-6 space-y-4 scrollbar-thin">
 <template x-for="msg in messages" :key="msg.id">
 <div class="flex flex-col" :class="msg.is_admin ? 'items-end' : 'items-start'">
 <div class="flex items-center space-x-1 mb-1">
 <span class="text-[10px] font-bold text-muted-foreground" x-text="msg.sender_name"></span>
 <span class="text-[9px] text-muted-foreground" x-text="msg.time"></span>
 </div>
 <div class="max-w-[75%] rounded-xl px-4 py-2.5 text-sm font-medium border animate-fade-in"
 :class="msg.is_admin ? 'bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground border-primary/25 rounded-tr-none' : 'bg-muted text-foreground border-border/60 rounded-tl-none'">
 <p class="whitespace-pre-wrap leading-relaxed break-words" x-text="msg.message"></p>
 </div>
 </div>
 </template>
 <div x-show="messages.length === 0" class="flex flex-col items-center justify-center py-12 text-center"
 x-cloak>
 <div
 class="w-10 h-10 bg-muted border border-border/60 rounded-xl flex items-center justify-center text-muted-foreground mb-2">
 <i data-lucide="message-square" class="size-4"></i>
 </div>
 <p class="text-xs text-muted-foreground font-semibold">No messages yet</p>
 <p class="text-[10px] text-muted-foreground mt-0.5">Send a message below to start the conversation with the
 buyer.</p>
 </div>
 </div>

 <!-- Chat Input Area -->
 <div class="p-4 border-t border-border bg-muted">
 <form @submit.prevent="sendMessage()" class="flex gap-2">
 <input type="text" x-model="newMessage" placeholder="Type a message to buyer..." required
 class="flex-1 px-4 py-2.5 border border-border rounded-lg text-sm focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card transition-all">
 <button type="submit" :disabled="sending"
 class="bg-primary hover:bg-primary/90 disabled:bg-muted text-primary-foreground font-medium px-5 py-2.5 rounded-lg text-sm transition-all flex items-center space-x-2">
 <span x-show="!sending">Send</span>
 <span x-show="sending" x-cloak>Sending...</span>
 <i data-lucide="send" class="size-4"></i>
 </button>
 </form>
 </div>
 </div>

 <script>
 function buyerChatManager() {
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
 const box = document.getElementById('buyer-chat-messages-box');
 if (box) {
 box.scrollTop = box.scrollHeight;
 }
 },
 async fetchMessages() {
 try {
 let res = await fetch("{{ route('admin.orders.chat.index', $order->id) }}");
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
 let res = await fetch("{{ route('admin.orders.chat.store', $order->id) }}", {
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
 </div>

 <!-- Right side: Financial, Delivery addresses, and Payout actions -->
 <div class="space-y-6">

 <!-- Financial Card -->
 <div class="bg-card border border-border rounded-xl p-6 ">
 <h3 class="font-bold text-foreground text-lg mb-6">Financial Ledger</h3>

 <div class="space-y-3 text-sm">
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Product Price</span>
 <span class="font-medium text-foreground">₹{{ number_format($order->product_amount, 2) }}</span>
 </div>
 <div class="flex justify-between items-center py-1.5 border-b border-border">
 <span class="text-muted-foreground">Shipping Cost</span>
 <span class="font-medium text-foreground">₹{{ number_format($order->shipping_amount, 2) }}</span>
 </div>
 <div class="flex justify-between items-center py-2.5 border-b border-border">
 <span class="font-bold text-foreground text-base">Total Escrow Paid</span>
 <span class="font-bold text-foreground text-base">₹{{ number_format($order->total_amount, 2) }}</span>
 </div>

 <div class="bg-muted p-4 border border-border rounded-xl space-y-2 mt-4 text-xs">
 <div class="flex justify-between items-center text-muted-foreground">
 <span>Platform Fee ({{ $order->commission_percent }}%)</span>
 <span
 class="font-medium text-foreground">₹{{ number_format($order->commission_amount, 2) }}</span>
 </div>
 <div
 class="flex justify-between items-center text-foreground font-semibold border-t border-border/60 pt-2">
 <span>Seller Payout Amount</span>
 <span>₹{{ number_format($order->seller_payout_amount, 2) }}</span>
 </div>
 </div>
 </div>
 </div>

 <!-- Shipping / Delivery Address -->
 <div class="bg-card border border-border rounded-xl p-6 ">
 <h3 class="font-bold text-foreground text-lg mb-4">Delivery Address</h3>
 <div class="text-sm text-muted-foreground space-y-1">
 <p class="font-bold text-foreground">{{ $order->delivery_address['name'] ?? 'N/A' }}</p>
 <p class="font-semibold text-foreground">{{ $order->delivery_address['phone'] ?? 'N/A' }}</p>
 <p class="text-muted-foreground mt-2">{{ $order->delivery_address['street'] ?? 'N/A' }}</p>
 <p class="text-muted-foreground">{{ $order->delivery_address['city'] ?? 'N/A' }},
 {{ $order->delivery_address['state'] ?? 'N/A' }} -
 {{ $order->delivery_address['pincode'] ?? 'N/A' }}</p>
 </div>
 </div>

 <!-- Users Contact -->
 <div class="bg-card border border-border rounded-xl p-6 ">
 <h3 class="font-bold text-foreground text-lg mb-4">Parties Contact</h3>
 <div class="space-y-4 text-xs">
 <div>
 <span class="text-muted-foreground font-semibold uppercase">Buyer</span>
 <div class="flex items-center justify-between mt-0.5">
 <p class="text-sm font-semibold text-foreground">{{ $order->buyer->name }}</p>
 @if($order->buyer->buyer_badge === 'trusted_buyer')
 <span
 class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted text-foreground border border-border/60 shadow-2xs">
 <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1 text-muted-foreground"></i> Trusted Buyer
 </span>
 @elseif($order->buyer->buyer_badge === 'verified_buyer')
 <span
 class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted text-foreground border border-border/60 shadow-2xs">
 <i data-lucide="badge-check" class="w-3.5 h-3.5 mr-1 text-muted-foreground"></i> Verified Buyer
 </span>
 @else
 <span
 class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted text-muted-foreground border border-border/60 shadow-2xs">
 <i data-lucide="user" class="w-3.5 h-3.5 mr-1 text-muted-foreground"></i> New Buyer
 </span>
 @endif
 </div>
 <p class="text-muted-foreground">{{ $order->buyer->email }}</p>
 </div>
 <div class="border-t border-border pt-3">
 <span class="text-muted-foreground font-semibold uppercase">Seller Shop</span>
 <p class="font-bold text-foreground text-sm mt-0.5">{{ $order->seller->shop_name }}</p>
 <p class="text-muted-foreground">{{ $order->seller->user->email }}</p>
 </div>
 </div>
 </div>

 <!-- Invoice Actions Panel -->
 <div class="bg-card border border-border rounded-xl p-6 space-y-4">
 <h3 class="font-bold text-foreground text-sm uppercase tracking-wider flex items-center gap-1.5">
 <i data-lucide="file-text" class="w-4 h-4 text-muted-foreground"></i> Invoice Management
 </h3>
 <div class="flex flex-col gap-2">
 <a href="{{ route('admin.orders.invoice.download', $order->id) }}"
 class="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-semibold py-2.5 px-4 rounded-lg ring-0 text-xs transition-all flex items-center justify-center space-x-2">
 <i data-lucide="download" class="size-4"></i>
 <span>Download Invoice</span>
 </a>
 <form action="{{ route('admin.orders.invoice.resend', $order->id) }}" method="POST">
 @csrf
 <button type="submit"
 class="w-full bg-primary hover:bg-primary text-primary-foreground font-semibold py-2.5 px-4 rounded-lg ring-0 text-xs transition-all flex items-center justify-center space-x-2">
 <i data-lucide="mail" class="size-4"></i>
 <span>Resend Invoice Email</span>
 </button>
 </form>
 </div>
 </div>

 </div>

 </div>
@endsection