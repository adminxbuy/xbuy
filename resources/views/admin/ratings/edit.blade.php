@extends('layouts.admin')

@section('title', 'Override Rating')
@section('page_title', 'Edit Rating Parameters')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Back button link -->
    <div class="mb-5">
        <a href="{{ route('admin.ratings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to Ratings Workspace</span>
        </a>
    </div>

    <!-- Rating edit card -->
    <div class="bg-card border border-border rounded-xl p-6 md:p-8 shadow-sm" x-data="{ 
        accuracy: {{ $rating->item_accuracy }}, 
        packaging: {{ $rating->packaging }}, 
        shipping: {{ $rating->shipping_speed }}, 
        communication: {{ $rating->communication }}, 
        get average() { 
            return ((parseFloat(this.accuracy) + parseFloat(this.packaging) + parseFloat(this.shipping) + parseFloat(this.communication)) / 4).toFixed(2); 
        } 
    }">
        <div class="flex items-center gap-3 border-b border-border pb-5 mb-6">
            <div class="p-2.5 bg-muted text-muted-foreground rounded-xl">
                <i data-lucide="edit-3" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-foreground text-sm">Override Rating #{{ $rating->id }}</h4>
                <p class="text-xs text-muted-foreground font-medium">Manually edit and recalculate rating scores for this transaction.</p>
            </div>
        </div>

        <!-- Read-only transaction context -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-muted border border-border/60 rounded-xl text-xs mb-6">
            <div>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">Order Number</span>
                <span class="font-bold text-foreground">{{ $rating->order->order_number ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">Seller (Supplier)</span>
                <span class="font-bold text-foreground">{{ $rating->seller->shop_name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">Buyer (Customer)</span>
                <span class="font-bold text-foreground">{{ $rating->buyer->name ?? 'N/A' }}</span>
            </div>
        </div>

        <form action="{{ route('admin.ratings.update', $rating->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Rating Type -->
                <div class="space-y-1.5">
                    <label for="rating_type" class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Rating Type</label>
                    <div class="relative">
                        <select name="rating_type" id="rating_type" required
                                class="w-full p-3 pl-4 pr-10 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all appearance-none cursor-pointer">
                            <option value="manual" {{ $rating->rating_type === 'manual' ? 'selected' : '' }}>Manual Override (Admin Created)</option>
                            <option value="auto" {{ $rating->rating_type === 'auto' ? 'selected' : '' }}>Automatic (System Generated)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- Weighted Live Preview Card -->
                <div class="bg-muted border border-border rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">Weighted Live Total</span>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-bold text-foreground" x-text="average">0.00</span>
                            <span class="text-xs text-muted-foreground">/ 5.00</span>
                        </div>
                    </div>
                    <div class="flex items-center text-yellow-400">
                        <i data-lucide="star" class="w-8 h-8 fill-current"></i>
                    </div>
                </div>
            </div>

            <!-- Dynamic sliders for individual metrics -->
            <div class="space-y-4 pt-4 border-t border-border">
                <h5 class="text-xs font-bold text-foreground uppercase tracking-wider">Metrics Parameters (1 - 5 Stars)</h5>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Item Accuracy -->
                    <div class="space-y-2 p-4 bg-muted border border-border/60 rounded-xl">
                        <div class="flex justify-between items-center text-xs">
                            <label for="item_accuracy" class="font-bold text-foreground">Item Accuracy</label>
                            <span class="px-2 py-0.5 bg-muted text-foreground font-bold rounded" x-text="accuracy">5</span>
                        </div>
                        <input type="range" name="item_accuracy" id="item_accuracy" min="1" max="5" step="0.5" x-model="accuracy"
                               class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-foreground">
                    </div>

                    <!-- Packaging Quality -->
                    <div class="space-y-2 p-4 bg-muted border border-border/60 rounded-xl">
                        <div class="flex justify-between items-center text-xs">
                            <label for="packaging" class="font-bold text-foreground">Packaging Quality</label>
                            <span class="px-2 py-0.5 bg-muted text-foreground font-bold rounded" x-text="packaging">5</span>
                        </div>
                        <input type="range" name="packaging" id="packaging" min="1" max="5" step="0.5" x-model="packaging"
                               class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-foreground">
                    </div>

                    <!-- Shipping Speed -->
                    <div class="space-y-2 p-4 bg-muted border border-border/60 rounded-xl">
                        <div class="flex justify-between items-center text-xs">
                            <label for="shipping_speed" class="font-bold text-foreground">Shipping Speed</label>
                            <span class="px-2 py-0.5 bg-muted text-foreground font-bold rounded" x-text="shipping">5</span>
                        </div>
                        <input type="range" name="shipping_speed" id="shipping_speed" min="1" max="5" step="0.5" x-model="shipping"
                               class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-foreground">
                    </div>

                    <!-- Communication -->
                    <div class="space-y-2 p-4 bg-muted border border-border/60 rounded-xl">
                        <div class="flex justify-between items-center text-xs">
                            <label for="communication" class="font-bold text-foreground">Communication</label>
                            <span class="px-2 py-0.5 bg-muted text-foreground font-bold rounded" x-text="communication">5</span>
                        </div>
                        <input type="range" name="communication" id="communication" min="1" max="5" step="0.5" x-model="communication"
                               class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-foreground">
                    </div>
                </div>
            </div>

            <!-- Review Text -->
            <div class="space-y-1.5 pt-2">
                <label for="review_text" class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Review Comments</label>
                <textarea name="review_text" id="review_text" rows="4" placeholder="Enter comments or reason for override here..."
                          class="w-full p-3 text-xs border border-border rounded-lg bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all @error('review_text') border-red-500 @enderror">{{ old('review_text', $rating->review_text) }}</textarea>
                @error('review_text')
                    <p class="text-red-500 text-[10px] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-border">
                <a href="{{ route('admin.ratings') }}" class="px-5 py-2.5 border border-border hover:bg-muted text-muted-foreground rounded-lg text-xs font-medium transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg text-xs font-medium transition-all shadow-sm">
                    Update & Recalculate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
