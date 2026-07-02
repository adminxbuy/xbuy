<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Order;
use App\Models\User;
use App\Models\SellerProfile;
use App\Jobs\SellerMetricsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    /**
     * Display a listing of ratings.
     */
    public function index(Request $request)
    {
        $query = Rating::with(['order', 'seller.user', 'buyer']);

        // Search in review text or order number
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('review_text', 'like', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Seller
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->input('seller_id'));
        }

        // Filter by Buyer
        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->input('buyer_id'));
        }

        // Filter by Rating Type
        if ($request->filled('rating_type')) {
            $query->where('rating_type', $request->input('rating_type'));
        }

        // Filter by Star Score (weighted_total)
        if ($request->filled('score')) {
            $score = $request->input('score');
            $query->whereBetween('weighted_total', [$score, $score + 0.99]);
        }

        Rating::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();
        $ratings = $query->orderBy('created_at', 'desc')->paginate(15);

        // Fetch all sellers and buyers for filters
        $sellers = SellerProfile::with('user')->get();
        $buyers = User::whereIn('role', ['buyer', 'user', 'hybrid'])->get();

        // Calculate some global statistics
        $stats = [
            'average_rating' => round(Rating::avg('weighted_total'), 2) ?: 0,
            'total_count' => Rating::count(),
            '5_star' => Rating::whereBetween('weighted_total', [4.5, 5])->count(),
            '4_star' => Rating::whereBetween('weighted_total', [3.5, 4.49])->count(),
            '3_star' => Rating::whereBetween('weighted_total', [2.5, 3.49])->count(),
            '2_star' => Rating::whereBetween('weighted_total', [1.5, 2.49])->count(),
            '1_star' => Rating::whereBetween('weighted_total', [0, 1.49])->count(),
        ];

        $trashedRatings = Rating::onlyTrashed()->with(['order', 'seller.user', 'buyer'])->get();

        return view('admin.ratings.index', compact('ratings', 'sellers', 'buyers', 'stats', 'trashedRatings'));
    }

    /**
     * Show the form for creating a new rating.
     */
    public function create()
    {
        // Only show orders that do not have ratings yet
        $orders = Order::whereDoesntHave('rating')
            ->with(['buyer', 'seller.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sellers = SellerProfile::with('user')->get();
        $buyers = User::whereIn('role', ['buyer', 'user', 'hybrid'])->get();

        return view('admin.ratings.create', compact('orders', 'sellers', 'buyers'));
    }

    /**
     * Store a newly created rating in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id|unique:ratings,order_id',
            'rating_type' => 'required|in:manual,auto',
            'item_accuracy' => 'required|numeric|between:1,5',
            'packaging' => 'required|numeric|between:1,5',
            'shipping_speed' => 'required|numeric|between:1,5',
            'communication' => 'required|numeric|between:1,5',
            'review_text' => 'nullable|string',
        ]);

        $order = Order::findOrFail($request->input('order_id'));

        $itemAccuracy = (float)$request->input('item_accuracy');
        $packaging = (float)$request->input('packaging');
        $shippingSpeed = (float)$request->input('shipping_speed');
        $communication = (float)$request->input('communication');
        
        $weightedTotal = round(($itemAccuracy + $packaging + $shippingSpeed + $communication) / 4, 2);

        Rating::create([
            'order_id' => $order->id,
            'seller_id' => $order->seller_id,
            'buyer_id' => $order->buyer_id,
            'rating_type' => $request->input('rating_type'),
            'item_accuracy' => $itemAccuracy,
            'packaging' => $packaging,
            'shipping_speed' => $shippingSpeed,
            'communication' => $communication,
            'weighted_total' => $weightedTotal,
            'review_text' => $request->input('review_text'),
            'is_public' => true,
        ]);

        // Recalculate metrics for the seller
        (new SellerMetricsJob())->handle();

        return redirect()->route('admin.ratings')->with('success', 'Rating manually added successfully.');
    }

    /**
     * Show the form for editing the specified rating.
     */
    public function edit(int $id)
    {
        $rating = Rating::with(['order', 'seller.user', 'buyer'])->findOrFail($id);
        return view('admin.ratings.edit', compact('rating'));
    }

    /**
     * Update the specified rating in storage.
     */
    public function update(Request $request, int $id)
    {
        $rating = Rating::findOrFail($id);

        $request->validate([
            'rating_type' => 'required|in:manual,auto',
            'item_accuracy' => 'required|numeric|between:1,5',
            'packaging' => 'required|numeric|between:1,5',
            'shipping_speed' => 'required|numeric|between:1,5',
            'communication' => 'required|numeric|between:1,5',
            'review_text' => 'nullable|string',
        ]);

        $itemAccuracy = (float)$request->input('item_accuracy');
        $packaging = (float)$request->input('packaging');
        $shippingSpeed = (float)$request->input('shipping_speed');
        $communication = (float)$request->input('communication');
        
        $weightedTotal = round(($itemAccuracy + $packaging + $shippingSpeed + $communication) / 4, 2);

        $rating->update([
            'rating_type' => $request->input('rating_type'),
            'item_accuracy' => $itemAccuracy,
            'packaging' => $packaging,
            'shipping_speed' => $shippingSpeed,
            'communication' => $communication,
            'weighted_total' => $weightedTotal,
            'review_text' => $request->input('review_text'),
            'overridden_at' => now(),
        ]);

        // Recalculate metrics for the seller
        (new SellerMetricsJob())->handle();

        return redirect()->route('admin.ratings')->with('success', 'Rating updated successfully. Seller metrics recalculated.');
    }

    /**
     * Remove the specified rating from storage.
     */
    public function destroy(int $id)
    {
        $rating = Rating::findOrFail($id);
        $rating->delete();

        // Recalculate metrics for the seller
        (new SellerMetricsJob())->handle();

        return redirect()->route('admin.ratings')->with('success', 'Rating deleted successfully. Seller metrics recalculated.');
    }
}
