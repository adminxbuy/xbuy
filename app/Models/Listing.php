<?php

namespace App\Models;

use App\Services\ListingNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = [
        'primary_image_url', 'warranty_summary', 'commission_rate',
        'testing_window_days', 'seo_title', 'seo_description', 'slug_url', 'is_previously_sold',
        'seo_keywords', 'og_image', 'schema_markup', 'highlighted_specs', 'all_specs', 'compatible_with'
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'sold_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // Relationships
    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class)->whereNotIn('order_status', ['cancelled', 'refunded']);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function specs()
    {
        return $this->hasMany(ListingSpec::class, 'listing_id')->orderBy('sort_order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('listing_status', 'active');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('listing_status', 'pending_approval');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeWithBadge($query, $badgeLevel)
    {
        return $query->whereHas('seller', function ($q) use ($badgeLevel) {
            $q->where('badge_level', $badgeLevel);
        });
    }

    public function scopeTrustedOnly($query)
    {
        return $query->where('trusted_buyers_only', true);
    }

    // Accessors
    public function getPrimaryImageUrlAttribute()
    {
        $primary = $this->images->where('is_primary', true)->first();
        return $primary ? $primary->image_url : null;
    }

    public function getWarrantySummaryAttribute()
    {
        if ($this->manufacturer_warranty_status === 'active') {
            return "{$this->manufacturer_warranty_months} Months Brand Warranty";
        }
        if ($this->seller_warranty_months) {
            return "{$this->seller_warranty_months} Months Seller Warranty";
        }
        return "No Warranty";
    }

    public function getCommissionRateAttribute()
    {
        $rates = json_decode(SiteSetting::where('key', 'commission_rates')->value('value'), true) ?? [];
        $categoryName = $this->attributes['category'] ?? '';
        return $rates[$categoryName] ?? 4;
    }

    public function getTestingWindowDaysAttribute()
    {
        $windows = json_decode(SiteSetting::where('key', 'testing_windows')->value('value'), true) ?? [];
        $categoryName = $this->attributes['category'] ?? '';
        return $windows[$categoryName] ?? 2;
    }

    public function getSeoTitleAttribute()
    {
        return "{$this->title} - Grade {$this->grade} | X-Buy.in";
    }

    public function getSeoDescriptionAttribute()
    {
        return "Buy verified {$this->category} on X-Buy.in. {$this->warranty_summary}. Escrow protected.";
    }

    public function getSlugUrlAttribute()
    {
        return url('/api/v1/listings/' . $this->slug);
    }

    public function getSeoKeywordsAttribute()
    {
        return "{$this->category}, {$this->brand}, {$this->model_name}, secondhand {$this->category}, buy {$this->category}";
    }

    public function getOgImageAttribute()
    {
        return $this->primary_image_url ?? url('images/default-og.png');
    }

    public function getSchemaMarkupAttribute()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->title,
            'image' => $this->og_image,
            'description' => $this->seo_description,
            'category' => $this->category,
            'offers' => [
                '@type' => 'Offer',
                'price' => $this->price,
                'priceCurrency' => 'INR',
                'availability' => 'https://schema.org/InStock',
                'url' => $this->slug_url,
            ]
        ];
    }

    public function getIsPreviouslySoldAttribute()
    {
        return \App\Models\SoldArchive::where('serial_number', $this->serial_number)->exists();
    }

    public function getHighlightedSpecsAttribute()
    {
        return $this->specs->where('is_highlighted', true)->values();
    }

    public function getAllSpecsAttribute()
    {
        return $this->specs->values();
    }

    public function getCompatibleWithAttribute()
    {
        $category = strtolower($this->category);
        $specs = $this->specs->pluck('spec_value', 'spec_key')->toArray();

        if ($category === 'gpu') {
            $interface = $specs['interface'] ?? 'PCIe 3.0/4.0';
            return "Compatible: {$interface}";
        }
        if ($category === 'cpu') {
            $socket = $specs['socket'] ?? 'LGA1700/AM4';
            return "Compatible: Socket {$socket}";
        }
        if ($category === 'motherboard') {
            $socket = $specs['socket'] ?? 'LGA1700/AM4';
            $form = $specs['form_factor'] ?? 'ATX';
            return "Compatible: {$socket} / {$form}";
        }
        if ($category === 'ram') {
            $type = $specs['ddr_type'] ?? 'DDR4';
            return "Compatible: {$type} Slots";
        }
        if ($category === 'storage') {
            $interface = $specs['interface'] ?? 'SATA/M.2';
            return "Compatible: {$interface}";
        }
        if ($category === 'psu') {
            $form = $specs['form_factor'] ?? 'ATX';
            return "Compatible: {$form} Cabinets";
        }
        return "Compatible: Universal";
    }

    protected static function booted()
    {
        // Auto-generate listing_number before first save
        static::creating(function ($listing) {
            if (empty($listing->listing_number)) {
                $categorySlug = $listing->category ?? 'other';
                // Need to be inside a transaction for lock safety;
                // the controller wraps creation in DB::transaction already.
                $listing->listing_number = ListingNumberService::generate($categorySlug);
            }
        });

        static::updating(function ($listing) {
            $statusWas = $listing->getOriginal('listing_status');
            $statusIs = $listing->listing_status;
            
            $catWas = $listing->getOriginal('category_id');
            $catIs = $listing->category_id;
            
            $brandWas = $listing->getOriginal('brand_id');
            $brandIs = $listing->brand_id;
            
            // If listing_status changes
            if ($statusWas !== $statusIs) {
                if ($statusIs === 'active') {
                    if ($catIs) \Illuminate\Support\Facades\DB::table('categories')->where('id', $catIs)->increment('listing_count');
                    if ($brandIs) \Illuminate\Support\Facades\DB::table('brands')->where('id', $brandIs)->increment('listing_count');
                } elseif ($statusWas === 'active') {
                    if ($catWas) \Illuminate\Support\Facades\DB::table('categories')->where('id', $catWas)->decrement('listing_count');
                    if ($brandWas) \Illuminate\Support\Facades\DB::table('brands')->where('id', $brandWas)->decrement('listing_count');
                }
            } elseif ($statusIs === 'active') {
                // If it was already active, and category/brand changed
                if ($catWas !== $catIs) {
                    if ($catWas) \Illuminate\Support\Facades\DB::table('categories')->where('id', $catWas)->decrement('listing_count');
                    if ($catIs) \Illuminate\Support\Facades\DB::table('categories')->where('id', $catIs)->increment('listing_count');
                }
                if ($brandWas !== $brandIs) {
                    if ($brandWas) \Illuminate\Support\Facades\DB::table('brands')->where('id', $brandWas)->decrement('listing_count');
                    if ($brandIs) \Illuminate\Support\Facades\DB::table('brands')->where('id', $brandIs)->increment('listing_count');
                }
            }
        });

        static::created(function ($listing) {
            if ($listing->listing_status === 'active') {
                if ($listing->category_id) {
                    \Illuminate\Support\Facades\DB::table('categories')->where('id', $listing->category_id)->increment('listing_count');
                }
                if ($listing->brand_id) {
                    \Illuminate\Support\Facades\DB::table('brands')->where('id', $listing->brand_id)->increment('listing_count');
                }
            }
        });

        static::deleted(function ($listing) {
            if ($listing->listing_status === 'active') {
                if ($listing->category_id) {
                    \Illuminate\Support\Facades\DB::table('categories')->where('id', $listing->category_id)->decrement('listing_count');
                }
                if ($listing->brand_id) {
                    \Illuminate\Support\Facades\DB::table('brands')->where('id', $listing->brand_id)->decrement('listing_count');
                }
            }
        });

        static::restored(function ($listing) {
            if ($listing->listing_status === 'active') {
                if ($listing->category_id) {
                    \Illuminate\Support\Facades\DB::table('categories')->where('id', $listing->category_id)->increment('listing_count');
                }
                if ($listing->brand_id) {
                    \Illuminate\Support\Facades\DB::table('brands')->where('id', $listing->brand_id)->increment('listing_count');
                }
            }
        });

        static::saved(function ($listing) {
            if ($listing->serial_number && in_array($listing->listing_status, ['active', 'pending_approval'])) {
                $duplicate = \App\Models\Listing::where('id', '!=', $listing->id)
                    ->whereIn('listing_status', ['active', 'pending_approval'])
                    ->where('serial_number', $listing->serial_number)
                    ->first();

                $archiveDuplicate = \App\Models\SoldArchive::where('serial_number', $listing->serial_number)->first();

                if ($duplicate || $archiveDuplicate) {
                    \App\Models\AdminAlert::firstOrCreate(
                        [
                            'type' => 'suspicious_activity',
                            'reference_type' => 'Listing',
                            'reference_id' => $listing->id,
                            'is_read' => false,
                        ],
                        [
                            'title' => 'Duplicate Serial Number Listed',
                            'message' => "The serial number '{$listing->serial_number}' has been listed multiple times. Active listings: #{$listing->id}." . ($duplicate ? " Active duplicate: #{$duplicate->id}." : "") . ($archiveDuplicate ? " Sold archive duplicate: #{$archiveDuplicate->id}." : ""),
                            'severity' => 'medium',
                        ]
                    );

                    $details = [
                        'serial_number' => $listing->serial_number,
                    ];
                    if ($duplicate) {
                        $details['active_duplicate_listing_id'] = $duplicate->id;
                        $details['active_duplicate_seller_id'] = $duplicate->seller_id;
                    }
                    if ($archiveDuplicate) {
                        $details['sold_archive_duplicate_id'] = $archiveDuplicate->id;
                        $details['sold_archive_duplicate_seller_id'] = $archiveDuplicate->seller_id;
                    }

                    \App\Models\FraudFlag::firstOrCreate(
                        [
                            'flag_type' => 'duplicate_serial',
                            'flagged_listing_id' => $listing->id,
                            'status' => 'pending'
                        ],
                        [
                            'flagged_user_id' => $listing->seller?->user_id,
                            'details' => $details,
                        ]
                    );
                }
            }

            // Trigger for Seller lists 10+ items in 1 hour
            if ($listing->seller_id) {
                $oneHourAgo = now()->subHour();
                $recentListingsCount = \App\Models\Listing::where('seller_id', $listing->seller_id)
                    ->where('created_at', '>=', $oneHourAgo)
                    ->count();

                if ($recentListingsCount >= 10) {
                    \App\Models\FraudFlag::firstOrCreate(
                        [
                            'flag_type' => 'rapid_listings',
                            'flagged_user_id' => $listing->seller?->user_id,
                            'status' => 'pending'
                        ],
                        [
                            'details' => [
                                'seller_id' => $listing->seller_id,
                                'listings_count_last_hour' => $recentListingsCount,
                            ]
                        ]
                    );
                }
            }
        });
    }
}
