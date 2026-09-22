# X-BUY: ULTIMATE FULL-FRONTEND MERCARI (mercari.com) MASTER SPECIFICATION & IMPLEMENTATION BIBLE

---

## 1. Executive Mission & System Architecture

### 1.1 The Objective
Build a complete, 100% production-ready, highly conversion-focused frontend for **X-Buy**, meticulously mirroring the design conventions, layout structure, ergonomics, and interactive behavior of **Mercari (mercari.com)**.

### 1.2 Core Architectural Non-Negotiables:
1. **Brand Authenticity:** While adopting Mercari's layout, card geometry, grid mechanics, bottom app docks, and split checkout funnels, retain X-Buy's distinctive visual identity:
   - **Primary Surface / Brand Accent:** `#FDD835` (X-Buy Brand Gold/Yellow).
   - **Foreground / Ink:** `#18181B` (High-contrast near-black on yellow = 13.5:1 AAA compliant).
   - **Light Surfaces / Canvas:** `#FFFFFF` canvas with `#F4F4F4` / `#F9F9FB` secondary utility blocks.
   - **Borders & Dividers:** Subtle zinc neutrals (`#E4E4E7` / `#D4D4D8`).
2. **Existing Core Auth & Account Retention:**
   - **Do NOT touch or break:** `/login`, `/register`, `/forgot-password`, `/reset-password` controllers and authentication token pipelines.
   - **User Dashboard (`/dashboard/*`):** Retain all 24 established profile, security, and wallet settings endpoints, seamlessly skinning their outer container, navigation links, and order timelines into Mercari's "My Page" visual hierarchy.
3. **Zero Orphan Pages & 100% Dynamic Admin Panel Linkage:**
   - **Every single footer link, header dropdown link, category card, banner, and policy document** must resolve dynamically to real database models (`SiteSetting`, `Category`, `Listing`, `Page`, `SupportTicket`, `Article`, `Order`, `Escrow`, `Wallet`).
   - No dead links, no placeholder URLs (`href="#"`), and no uncached `env()` calls in Blade files.

---

## 2. Complete Inventory of All Frontend Pages & View Matrix

Every single page of the frontend is mapped out below with its route, Blade view path, and Mercari-inspired layout model:

| Page Category | Page Name | URL / Route Pattern | Target Blade File | Mercari Reference Feature |
| :--- | :--- | :--- | :--- | :--- |
| **Global Shell** | Global Header & Mega Menu | `/*` | `resources/views/layouts/partials/header.blade.php` | Omnibar search, Category Mega Menu, Wishlist & Cart drawers, Sell button |
| **Global Shell** | Mobile Bottom Dock | `/* (< 1024px)` | `resources/views/layouts/partials/header/mobile-drawer.blade.php` | 5-button native bottom bar (Home, Explore, Sell, Alerts, Profile) |
| **Global Shell** | Global Master Footer | `/*` | `resources/views/layouts/partials/footer.blade.php` | 4-Column directory + Mobile App Download Teaser + Payment Badges |
| **Discovery** | Homepage | `/` | `resources/views/welcome.blade.php` | Hero promo carousel, 3-Card Category Grid, 4-Card Teasers, Live Feed |
| **Catalog** | Search & Catalog Browse | `/listings` | `resources/views/listings/index.blade.php` | Sticky filter sidebar (Category, Brand, Condition, Price, Free Shipping) |
| **Catalog** | Category Hub / Landing | `/categories/{slug}` | `resources/views/categories/show.blade.php` | Category banner, Sub-category pill carousel, Filtered product grid |
| **Catalog** | Brand Directory & Hub | `/brands/{slug}` | `resources/views/brands/show.blade.php` | Brand hero banner, verified authenticator badge, brand product grid |
| **Product** | Single Product Details | `/listings/{slug}` | `resources/views/listings/show.blade.php` | 1:1 Media reel, Dual CTAs ("Buy Now" & "Make an Offer"), Escrow Card |
| **Selling** | Sell an Item (Wizard) | `/dashboard/listings/create` | `resources/views/dashboard/listings/create.blade.php` | 3-minute listing flow, 10-photo uploader, Live fee & payout calculator |
| **Selling** | Edit Active Listing | `/dashboard/listings/{id}/edit` | `resources/views/dashboard/listings/edit.blade.php` | Real-time photo reordering, price reduction suggestions |
| **Cart & Checkout**| Slide-out Cart Drawer | Global Alpine Drawer | `resources/views/components/frontend/cart-drawer.blade.php` | Bundled items by seller, coupon code field, instant subtotal |
| **Cart & Checkout**| Direct Checkout Flow | `/checkout/{order_id?}` | `resources/views/checkout/index.blade.php` | 4-step accordion: Address ➔ Delivery ➔ Wallet/Razorpay ➔ Place Order |
| **Cart & Checkout**| Order Placed / Success | `/checkout/success/{id}` | `resources/views/checkout/success.blade.php` | Escrow held banner, AWB tracking link, "Message Seller" CTA |
| **Negotiation** | Make an Offer Dialog | Modal on `/listings/{slug}` | `resources/views/components/frontend/offer-modal.blade.php` | 5%, 10%, 15% discount chips + custom price offer input |
| **Orders & Escrow**| My Purchases (Buyer) | `/dashboard/orders?tab=buying` | `resources/views/dashboard/orders.blade.php` | 5-stage shipment stepper, 7-day inspection countdown, Escrow Release |
| **Orders & Escrow**| My Sales (Seller) | `/dashboard/orders?tab=selling` | `resources/views/dashboard/orders.blade.php` | Printable shipping label, dispatch confirmation, payout ledger |
| **Disputes** | Raise & Manage Dispute | `/dashboard/orders/{id}/dispute`| `resources/views/dashboard/disputes/create.blade.php` | Guided return wizard: reason picker, photo evidence, escrow freeze |
| **Public Store** | Seller Public Storefront| `/shop/{seller_slug}` | `resources/views/sellers/public-store.blade.php` | Seller banner, bio, verified badges, rating stars, active inventory |
| **Content & CMS** | Policy & Help Pages (20)| `/p/{slug}` | `resources/views/pages/show.blade.php` | Clean documentation layout, category sidebar, breadcrumbs, search |
| **Support** | Help Desk & Tickets | `/p/contact-us` | `resources/views/pages/contact.blade.php` | Live category ticket submission, ticket status tracking |
| **Editorial** | Blog & Tech Guides | `/blog` & `/blog/{slug}` | `resources/views/blog/index.blade.php` | Hardware benchmarks, buying guides, sales alerts, newsletter subscribe |

---

## 3. Global Navigation, Header, Search & Footer (Mercari Style)

### 3.1 Two-Tier Desktop Header (`layouts/partials/header.blade.php`)

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [ X-BUY Logo ]   [ 🔍 Search for anything (e.g. RTX 4090, iPhone 15)...                      ]         │
│                  └──────────────────────────────────────────────────────────────────────────┘         │
│                  [ + Sell on X-Buy ]   [ ♡ Wishlist ]   [ 🛒 Cart (3) ]   [ 🔔 Alerts ]   [ User Menu ]│
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ [ ☰ All Categories ]   Electronics   GPUs & PC Parts   Smartphones   Gaming   Deals 🔥   Free Shipping  │
└────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

#### Detailed Element Specifications:
1. **Brand Logo:** Embedded SVG or dynamic `SiteSetting::getVal('website_logo')` with height fixed to `38px`.
2. **Mercari Omnibar Search Component:**
   - **Geometry:** Height `44px`, rounded pill (`rounded-full`), border `border-zinc-300 focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900/10`.
   - **Auto-Suggest Drawer (Alpine.js `x-show="searchFocused"`):**
     - Section A: **Recent Searches** (Stored in `localStorage` with clear button).
     - Section B: **Popular on X-Buy** (e.g., `RTX 4080 Super`, `PlayStation 5 Pro`, `Mechanical Keyboards`, `OLED Monitors`).
     - Section C: **Direct Category Matches** (Instant autocomplete matching DB `categories`).
3. **Header Quick-Action Buttons:**
   - **"Sell on X-Buy" Button:** Pill CTA `bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold px-6 py-2.5 rounded-full text-sm shadow-sm transition-transform active:scale-95`.
   - **Wishlist Heart Pill:** Shows active count badge (red dot or number); clicking opens side drawer or redirects to saved items.
   - **Cart Pill:** Shows cart count bubble with smooth scale bounce when an item is added.
   - **User Menu Dropdown:** If guest, renders clean "Log in" and "Sign up" buttons. If logged in, shows user avatar, name, verification badge, and quick links:
     - *My Purchases*
     - *My Sales & Listings*
     - *Wallet Balance: ₹XX,XXX*
     - *Account Settings*
     - *Admin Panel* (if `role === 'admin'`)
     - *Log Out*

4. **Category Strip & Hover Mega-Menu:**
   - Horizontal scrollable list on desktop.
   - Hovering over "All Categories" opens a 3-column Mega Menu:
     - **Column 1:** Parent Categories with Lucide icons (Electronics, PC Components, Gaming, Mobiles, Collectibles).
     - **Column 2:** Child Subcategories (e.g. For PC Components: Graphics Cards, Processors, Motherboards, RAM, Power Supplies, Cabinets).
     - **Column 3:** Featured Brands in that category (e.g. ASUS ROG, MSI, Gigabyte, ZOTAC, Corsair).

### 3.2 Mobile-First Navigation & Bottom App Dock (< 1024px)
Mercari's mobile navigation is designed like a native iOS/Android application:
- **Top Bar:** Sticky compact header with Hamburger drawer, Logo, and Search bar.
- **Fixed Bottom Navigation Bar (`fixed bottom-0 inset-x-0 bg-white border-t border-zinc-200 z-50 h-16`):**
  - **Button 1 (Home):** `lucide-home` -> `/`
  - **Button 2 (Explore):** `lucide-compass` -> `/listings`
  - **Button 3 (Sell - Highlighted Center):** Raised floating circle button in Brand Yellow (`w-12 h-12 bg-[#FDD835] rounded-full shadow-lg flex items-center justify-center -translate-y-3 border-4 border-white`) -> `/dashboard/listings/create`.
  - **Button 4 (Alerts):** `lucide-bell` -> `/dashboard/notifications`
  - **Button 5 (Profile / My Page):** `lucide-user` -> `/dashboard/profile`

---

## 4. Mercari-Inspired Homepage Architecture (`welcome.blade.php`)

The homepage must avoid generic corporate landing page cliches and deliver pure e-commerce discovery:

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [ Hero Banner Slider: "Sell PC Gear with 0% Fee" / "Summer Escrow Deals" - Rounded 2XL ]               │
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ [ 3-Card Category Grid: Mercari High-Velocity Visual Teasers ]                                        │
│  ┌───────────────────────┐  ┌───────────────────────┐  ┌───────────────────────┐                     │
│  │ Graphics Cards (GPUs) │  │ Processors (CPUs)     │  │ Motherboards & RAM    │                     │
│  └───────────────────────┘  └───────────────────────┘  └───────────────────────┘                     │
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ [ 4-Card Category Teasers: Electronics | Gaming Gear | Audio | Accessories ]                          │
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ 🛡️ X-Buy Escrow Buyer Protection Banner: "Inspect for 7 days before seller gets paid"                 │
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ 🔥 Recently Listed (Live 5-Column Grid with Heart Wishlist & 1:1 Photos)                               │
├────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ 🏷️ Popular Brands on X-Buy (Apple, ASUS, MSI, Sony, NVIDIA, Samsung)                                   │
└────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 4.1 Section Breakdown:
1. **Hero Promo Banner Slider:**
   - Full-width rounded card carousel (`rounded-2xl overflow-hidden border border-zinc-200`) powered by Alpine.js with auto-play (5s) and manual dot controls.
   - Backed by `SiteSetting` or dynamic campaign banners.
2. **Three-Card Category Highlight Cards (Mercari Signature Style):**
   - 3 large aspect-ratio cards with high-contrast typography and subtle hover zoom (`group-hover:scale-[1.02]`).
3. **Four-Card Full-Bleed Category Blocks:**
   - Square tiles with category name and count badge (`"142 items"`).
4. **Trust & Escrow Guarantee Banner:**
   - 3-pillar banner:
     - *100% Escrow Protection:* Money is held safe until delivery.
     - *7-Day Testing Window:* Real hardware stress-testing time.
     - *Verified Sellers & Shiprocket Tracking:* Door-to-door insured transit.
5. **"Recently Listed" Product Feed:**
   - Dynamic 5-column grid (`grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4`).
   - Uses the standardized `<x-product-card>` component.

---

## 5. The Standardized Product Card Component (`<x-product-card>`)

Every product shown across the homepage, catalog search, category pages, brand pages, and related items carousels must use this exact Mercari-style component:

```blade
{{-- resources/views/components/frontend/product-card.blade.php --}}
@props(['listing'])

<div class="group relative flex flex-col bg-white rounded-xl border border-zinc-200/80 overflow-hidden hover:shadow-lg hover:border-zinc-300 transition-all duration-200"
     x-data="{ isLiked: {{ auth()->check() && auth()->user()->hasFavorited($listing->id) ? 'true' : 'false' }} }">
    
    <!-- 1:1 Aspect Ratio Photo Container -->
    <a href="{{ route('listings.show', $listing->slug) }}" class="relative block aspect-square w-full bg-zinc-100 overflow-hidden">
        <img 
            src="{{ $listing->primary_image_url ?? ($listing->images->first()->image_url ?? '/website_assets/images/placeholder.png') }}" 
            alt="{{ $listing->title }}"
            loading="lazy"
            class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
        />

        <!-- Top-Right Floating Wishlist Heart -->
        <button 
            type="button"
            @click.prevent.stop="
                @if(auth()->check())
                    isLiked = !isLiked;
                    fetch('/api/buyer/wishlist/toggle', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ listing_id: {{ $listing->id }} })
                    });
                @else
                    window.location.href = '{{ route('login') }}';
                @endif
            "
            class="absolute top-2.5 right-2.5 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 backdrop-blur-xs text-zinc-600 shadow-sm transition-all hover:bg-white hover:scale-110 active:scale-95"
            aria-label="Save to Wishlist"
        >
            <svg class="h-4.5 w-4.5 transition-colors" :class="isLiked ? 'fill-red-500 text-red-500' : 'text-zinc-600 stroke-2'" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <!-- Bottom-Left Badge: Free Shipping / Condition -->
        <div class="absolute bottom-2 left-2 flex flex-wrap gap-1">
            @if($listing->shipping_fee == 0)
                <span class="rounded-md bg-zinc-900/80 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                    Free Ship
                </span>
            @endif
            @if($listing->condition)
                <span class="rounded-md bg-white/90 backdrop-blur-xs px-2 py-0.5 text-[10px] font-semibold text-zinc-800 capitalize shadow-xs">
                    {{ str_replace('_', ' ', $listing->condition) }}
                </span>
            @endif
        </div>

        <!-- Sold Overlay Banner -->
        @if($listing->listing_status === 'sold')
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-10">
                <span class="bg-red-600 text-white font-extrabold text-sm uppercase px-4 py-1.5 rounded-md shadow-lg tracking-widest transform -rotate-6">
                    SOLD
                </span>
            </div>
        @endif
    </a>

    <!-- Card Content Details -->
    <div class="flex flex-col flex-1 p-3">
        <!-- Price & MSRP -->
        <div class="flex items-baseline gap-1.5 mb-1">
            <span class="text-base font-extrabold text-zinc-950 tracking-tight">
                ₹{{ number_format($listing->price) }}
            </span>
            @if($listing->original_price && $listing->original_price > $listing->price)
                <span class="text-xs text-zinc-400 line-through">
                    ₹{{ number_format($listing->original_price) }}
                </span>
            @endif
        </div>

        <!-- Truncated 2-Line Title -->
        <a href="{{ route('listings.show', $listing->slug) }}" class="text-xs sm:text-sm font-medium text-zinc-800 line-clamp-2 leading-snug hover:underline group-hover:text-zinc-950 mb-2">
            {{ $listing->title }}
        </a>

        <!-- Bottom Meta: Brand & Location -->
        <div class="mt-auto pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-500 font-medium">
            <span class="truncate max-w-[120px]">{{ $listing->brand ? $listing->brand->name : ($listing->brand_text ?? 'Verified Gear') }}</span>
            <span class="text-zinc-400">{{ $listing->created_at->diffForHumans(null, true, true) }}</span>
        </div>
    </div>
</div>
```

---

## 6. Catalog Search & Filter Page (`listings/index.blade.php`)

Mercari’s catalog search page is built for lightning-fast item discovery with responsive multi-attribute filtering:

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Breadcrumbs: Home > Electronics > PC Components > Graphics Cards                                      │
├────────────────────────────────────────────────┬───────────────────────────────────────────────────────┤
│ ☰ FILTERS (Left Sticky Sidebar - 280px)        │ Top Sort Strip: "1,420 Results" | Sort: Best Match ▾  │
│                                                ├───────────────────────────────────────────────────────┤
│ [ Active Filter Pills: RTX 4080 ✕ ] [ Under 50k ✕ ] [ Free Shipping ✕ ] [ Clear All ]                 │
│                                                ├───────────────────────────────────────────────────────┤
│ ▼ Category                                     │                                                       │
│   • Graphics Cards (820)                       │  [Card]  [Card]  [Card]  [Card]                       │
│   • Processors (310)                           │                                                       │
│                                                │  [Card]  [Card]  [Card]  [Card]                       │
│ ▼ Brand (Searchable list)                      │                                                       │
│   [ ] ASUS (210)                               │  [Card]  [Card]  [Card]  [Card]                       │
│   [ ] MSI (185)                                │                                                       │
│   [ ] ZOTAC (92)                               │                                                       │
│                                                │                                                       │
│ ▼ Price Range                                  │                                                       │
│   [ Min ₹ ] to [ Max ₹ ] [ Apply ]             │                                                       │
│   Pills: Under ₹5k | ₹5k-₹20k | ₹20k-₹50k      │                                                       │
│                                                │                                                       │
│ ▼ Condition                                    │                                                       │
│   [ ] New with box  [ ] Like New               │                                                       │
│   [ ] Good          [ ] Fair                   │                                                       │
│                                                │                                                       │
│ ▼ Shipping Options                             │                                                       │
│   [ ] Free Shipping Only                       │                                                       │
│   [ ] Local Pickup                             │                                                       │
│                                                │                                                       │
│ ▼ Availability                                 │                                                       │
│   (•) For Sale      ( ) Sold Items             │                                                       │
└────────────────────────────────────────────────┴───────────────────────────────────────────────────────┘
```

### Detailed Filter Mechanics:
- **Instant Reactive Query Sync:** Filters bind cleanly to GET parameters (`?search=...&category=...&brand=...&min_price=...&max_price=...&condition=...&free_shipping=1&status=active`).
- **Mobile Filter Drawer:** On mobile viewports (< 768px), the sidebar collapses into a floating bottom button: `"Filter & Sort (3 Active)"`. Tapping slides up an iOS-style bottom sheet with full touch controls and a fixed "Show 1,420 Results" button.

---

## 7. Single Product Page & The "Make an Offer" Flow (`listings/show.blade.php`)

Mercari’s product page architecture is engineered around buyer confidence and fast negotiation:

### 7.1 Page Layout & Structure
1. **Left Column (Media Gallery - 60% Width):**
   - Main viewport displaying ultra-crisp photo.
   - Smooth horizontal thumbnail reel (supports 1 to 10 images).
   - Zoom lens on desktop hover; touch swipe on mobile.
2. **Right Column (Sticky Conversion Card - 40% Width):**
   - **Breadcrumb & Category Path:** Home > Category > Subcategory.
   - **Title:** Bold `text-2xl font-extrabold text-zinc-950`.
   - **Pricing Block:**
     - Current Price: `₹45,000` (Huge, high-contrast).
     - Original Retail / MSRP: `₹59,999` with `25% OFF` green badge.
     - Tax & Delivery: `"Free Shipping • Insured by Shiprocket"`.
   - **Primary Action Stack (Mercari Signature Dual Buttons):**
     - **Button A (Buy Now):**
       `class="w-full h-13 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-lg rounded-xl shadow-sm flex items-center justify-center gap-2 transition-transform active:scale-98"`
       Directly routes to instant checkout (`/checkout?listing_id=...`).
     - **Button B (Make an Offer):**
       `class="w-full h-12 bg-white border-2 border-zinc-900 text-zinc-950 hover:bg-zinc-50 font-bold text-base rounded-xl flex items-center justify-center gap-2 transition-transform active:scale-98"`
       Triggers the interactive **Offer Modal**.
     - **Button C (Add to Cart):**
       Outline button adding item to the slide-out cart drawer.
   - **Buyer Protection Escrow Guarantee Card:**
     - Bordered card with shield icon:
       *"Your payment is held in X-Buy Escrow. We do not release money to the seller until you receive the package, inspect it for 7 days, and confirm it works."*
   - **Seller Verification Profile Card:**
     - Seller shop name, avatar, joined date.
     - Rating Score: `★ 4.9 (124 ratings)`.
     - Badges: `Verified Seller`, `Fast Shipper` (Ships within 24 hrs), `Top Rated`.
     - "Chat with Seller" messaging button.

### 7.2 The "Make an Offer" Modal Specification

```
┌────────────────────────────────────────────────────────┐
│ Make an Offer on "ASUS ROG Strix RTX 3080 10GB"        │
├────────────────────────────────────────────────────────┤
│ Listed Price: ₹42,000                                  │
│                                                        │
│ Quick Discount Offers:                                 │
│ [ 5% Off: ₹39,900 ]  [ 10% Off: ₹37,800 ]  [ 15% Off: ₹35,700 ]
│                                                        │
│ Or Enter Custom Offer:                                 │
│ [ ₹  Enter your offer amount (Minimum ₹33,600)       ] │
│                                                        │
│ ℹ️ Offers are valid for 24 hours. If the seller accepts,│
│ your order will be created and held in Escrow.         │
│                                                        │
│ [ Send Offer to Seller ]                               │
└────────────────────────────────────────────────────────┘
```
- **Rules:** Offers lower than 80% of listing price are blocked to prevent low-ball spam. When submitted, notifies the seller via SMS/Email and creates a pending negotiation state.

---

## 8. High-Speed Sell Item Listing Wizard (`/dashboard/listings/create`)

Mercari’s listing flow allows sellers to publish an item in under 3 minutes:

1. **Step 1: Visual Photo Dropzone:**
   - Drag & drop up to 10 photos.
   - Re-arrangeable thumbnail cards using SortableJS.
   - Automatic thumbnail badge: "Cover Photo".
2. **Step 2: Item Information & Auto-Categorization:**
   - **Item Title:** Auto-suggests based on brand + category inputs.
   - **Category Cascader:** Dynamic Parent -> Child category selector.
   - **Brand Search:** Autocomplete brand picker connected to `brands` table.
   - **Condition Segmented Pills:**
     - `New (Unopened)`
     - `Like New (No signs of wear)`
     - `Good (Gently used)`
     - `Fair (Visible cosmetic wear)`
     - `For Parts / Not Working`
3. **Step 3: Dynamic Technical Specifications:**
   - If user selects "Graphics Cards", dynamic fields from `spec_templates` render automatically:
     - Chipset (e.g. RTX 3080, RX 6800 XT)
     - VRAM Size (e.g. 10GB GDDR6X)
     - Power Connectors (e.g. 2x 8-pin)
     - Warranty Remaining (Months)
4. **Step 4: Transparent Fee & Payout Calculator:**
   - User types listing price: `₹20,000`.
   - Live real-time deduction:
     - Listing Price: `₹20,000`
     - Platform Commission (`SiteSetting::getVal('commission_percentage')`%): `-₹1,000`
     - Courier Fee: Paid by Buyer (`₹0`)
     - **Net Payout to Your Bank:** `<span class="text-emerald-600 font-extrabold text-xl">₹19,000</span>`
5. **Step 5: Shipping Preferences:**
   - Package weight category (Under 0.5kg, 1kg, 2kg, 5kg+).
   - Pickup address selector (saved seller warehouse/home address).
6. **Submit:** Instant publish with automated image WebP optimization via `ImageOptimizer` service.

---

## 9. Slide-Out Cart Drawer & Direct Checkout Flow

### 9.1 Slide-Out Cart Drawer (`<x-cart-drawer>`)
- Accessible from any page via the cart icon.
- Slides out smoothly from the right (`translate-x-0`).
- Groups items by seller (so shipping and bundling are clearly communicated).
- Shows item thumbnail, title, price, quantity, and trash icon.
- Subtotal with dynamic shipping calculation.
- Button: `"Proceed to Checkout (X items) - ₹XX,XXX"`.

### 9.2 The Modern Checkout Flow (`/checkout`)

Following Mercari's seamless 4-step accordion checkout:

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ CHECKOUT                                                                      ORDER SUMMARY            │
├───────────────────────────────────────────────────────────────────────────────┬────────────────────────┤
│ 1. DELIVERY ADDRESS                                              [ Change ]   │ 1 Item: RTX 3080       │
│    John Doe • +91 98765 43210                                                 │ Product:       ₹42,000 │
│    Flat 402, Sunset Heights, Bandra West, Mumbai, MH - 400050                 │ Shipping:         FREE │
├───────────────────────────────────────────────────────────────────────────────┤ Protection Fee:   ₹150 │
│ 2. DELIVERY METHOD                                                            │ Total:         ₹42,150 │
│    (•) Insured Standard Delivery via Shiprocket (Est: 2-3 Days)        FREE   │                        │
├───────────────────────────────────────────────────────────────────────────────┤ 🛡️ Escrow Guarantee:   │
│ 3. PAYMENT METHOD                                                             │ Funds held safely      │
│    [✓] Use X-Buy Wallet Balance (Available: ₹5,000)      -₹5,000 applied      │ until delivery is      │
│    Remaining to Pay: ₹37,150                                                  │ confirmed.             │
│                                                                               │                        │
│    Select Payment Gateway:                                                    │ [ Pay & Place Order ]  │
│    (•) Razorpay Secure (UPI, Cards, NetBanking, EMI)                          │                        │
├───────────────────────────────────────────────────────────────────────────────┴────────────────────────┤
│ 4. ESCROW TERMS AGREEMENT                                                                              │
│    [✓] I understand my payment will be held safely in X-Buy Escrow during the 7-day testing window.     │
└────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 10. Buyer & Seller Order Management & 7-Day Escrow Stepper (`/dashboard/orders`)

Mercari’s post-purchase order screen gives total transparency to both buyer and seller:

### 10.1 Order Status Stepper Bar:
A visual 5-node timeline with animated pulse on the active stage:
```
  [✓ Payment Confirmed] ───> [✓ Label Generated] ───> [✓ In Transit] ───> [ 📦 Delivered / Testing ] ───> [ Completed ]
```

### 10.2 The 7-Day Testing Window & Escrow Action Panel:
When the shipment status reaches `delivered`, the order transitions to `testing_period`:
- **Countdown Badge:**
  `"⏳ 4 Days, 18 Hours remaining in your testing window"`
- **Inspection Checklist:**
  - Check item physical condition against listing photos.
  - Test functionality, benchmark performance, verify serial numbers.
- **Two Big Action Buttons:**
  1. **"Confirm Delivery & Release Funds" (Green Button):**
     - Pops up rating modal: Give 1 to 5 stars, select tags (*Accurate Description*, *Fast Shipping*, *Great Packaging*), and write feedback.
     - Releases Escrow payout to seller instantly.
  2. **"Item Issue? Raise Dispute" (Red Outline Button):**
     - Opens the Dispute Wizard. Freezes escrow auto-release timer immediately.

---

## 11. Complete Dynamic Policy Pages & Help Center Directory

Every single footer and legal link connects to dynamic database records managed in the Admin Panel (`Page` model):

### 11.1 All 20 Platform Policy Slugs & Templates:

| Section | Page Slug | Title | Key Mercari Feature Highlighted |
| :--- | :--- | :--- | :--- |
| **Trust & Safety** | `buyer-protection` | X-Buy Buyer Protection Guarantee | Explanation of 7-day inspection window and 100% money-back escrow. |
| **Trust & Safety** | `seller-guidelines`| Seller Protection & Payouts | Seller insurance against shipping damages, verified delivery receipts. |
| **Trust & Safety** | `escrow-policy` | Escrow Financial Operations | How funds are held with Razorpay Escrow accounts until release. |
| **Trust & Safety** | `safety-guidelines`| Prohibited Items & Safety | Ban on counterfeit goods, stolen hardware, guns, and hazardous gear. |
| **Trust & Safety** | `authenticate` | Hardware Authentication Guide | How to verify genuine GPU/CPU serial numbers and avoid fake chips. |
| **Shipping** | `shipping` | Shipping Made Easy | Shiprocket prepaid label creation, tracking updates, insurance claims. |
| **Shipping** | `packaging` | Packaging Best Practices | Anti-static bags, bubble wrap, double-boxing rules for electronics. |
| **Orders & Returns**| `refund-policy` | Refunds and Returns Policy | Step-by-step return label generation, 7-day dispute arbitration rules. |
| **Financials** | `getting-paid` | How Payouts Work | Direct bank deposit (NEFT/IMPS) timelines, zero transfer fees. |
| **Financials** | `coupons-and-promotions`| Coupons & Promo Terms | Rules for discount vouchers, referral credits, and promotional sales. |
| **Support** | `contact-us` | Contact Customer Support | Integrated support ticket creation form with department routing. |
| **Support** | `faqs` | Help Center & FAQ | Searchable accordion FAQs categorized into Buying, Selling, Shipping. |
| **Support** | `service-status` | Platform & Gateway Status | Live uptime status for Payments (Razorpay), Shipping, and SMS. |
| **Discovery** | `how-it-works` | How X-Buy Works | 3-step visual infographic for buying and selling safely. |
| **Discovery** | `deals` | Hot Deals & Price Drops | Filtered catalog showing listings with 20%+ price reductions. |
| **Discovery** | `gift-card-exchange`| Gift Cards & Wallet Credits | How to redeem gift cards and utilize internal wallet balance. |
| **Company** | `about-us` | About X-Buy Marketplace | Mission statement, leadership team, community ethos. |
| **Company** | `careers` | Careers at X-Buy | Open positions, engineering roles, employee benefits. |
| **Legal** | `terms-of-service` | Terms of Service | User agreement, acceptable use, liability limitations. |
| **Legal** | `privacy-policy` | Privacy Policy | DPDP & GDPR compliant data privacy, cookie tracking policies. |
| **Legal** | `cookie-preferences`| Cookie Preferences Manager | Interactive cookie toggle modal (Strictly Necessary, Analytics, Marketing).|
| **Legal** | `licenses-and-disclosures`| Legal Licenses & Disclosures | Commercial registry numbers, payment facilitator compliance disclosures.|

---

## 12. Standardized Global Footer (`layouts/partials/footer.blade.php`)

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ [ X-BUY Logo ]   The safest community marketplace for electronics, PC components & verified gear.      │
├───────────────────┬───────────────────┬───────────────────┬────────────────────────────────────────────┤
│ SHOP              │ SELL              │ SUPPORT           │ COMPANY & LEGAL                            │
│ • Trending Gear   │ • How to Sell     │ • Help Center     │ • About Us                                 │
│ • Brands Directory│ • Packaging Guide │ • Contact Us      │ • Careers                                  │
│ • Categories      │ • Shipping Rates  │ • Buyer Protection│ • Terms of Service                         │
│ • Deals & Drops   │ • Getting Paid    │ • Return Policy   │ • Privacy Policy                           │
│ • How it Works    │ • Authenticate    │ • Safety Rules    │ • Escrow Policy                            │
├───────────────────┴───────────────────┴───────────────────┴────────────────────────────────────────────┤
│ 💳 Secure Payment Partners: Razorpay • UPI (GPay/PhonePe) • Visa • Mastercard • NetBanking             │
│ 🚚 Insured Shipping Powered by: Shiprocket Logistics Network                                          │
│ © 2026 X-Buy Technologies Pvt Ltd. All rights reserved. Built with pride.                               │
└────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 13. Step-by-Step Implementation & Delivery Roadmap

```
Sprint 1: Global Shell & Design System (Days 1–3)
 ├── Task 1.1: Standardize resources/css/frontend.css with Mercari tokens and X-Buy Yellow palette.
 ├── Task 1.2: Build layouts/partials/header.blade.php with Omnibar search and Mega-Menu flyout.
 ├── Task 1.3: Build Mobile Bottom App Dock for native-like handheld browsing.
 └── Task 1.4: Refactor layouts/partials/footer.blade.php connecting all 22 links to dynamic DB routes.

Sprint 2: Homepage & Catalog Search Experience (Days 4–7)
 ├── Task 2.1: Rebuild welcome.blade.php with Mercari 3-card and 4-card category teasers.
 ├── Task 2.2: Implement <x-product-card> with 1:1 square ratio, wishlist toggle, and sold ribbons.
 ├── Task 2.3: Rebuild listings/index.blade.php with sticky multi-attribute filter sidebar.
 └── Task 2.4: Implement mobile filter drawer bottom sheet.

Sprint 3: Product Detail, Negotiation & Cart (Days 8–11)
 ├── Task 3.1: Rebuild listings/show.blade.php with high-res photo reel and verified seller trust card.
 ├── Task 3.2: Implement "Make an Offer" Alpine.js negotiation modal with quick discount pills.
 ├── Task 3.3: Build Slide-Out Cart Drawer (<x-cart-drawer>) with seller item grouping.
 └── Task 3.4: Rebuild /checkout with 4-step accordion (Address, Shiprocket, Wallet/Razorpay, Terms).

Sprint 4: Post-Purchase, Orders & Escrow Stepper (Days 12–14)
 ├── Task 4.1: Rebuild /dashboard/orders with 5-stage shipment stepper and 7-day countdown.
 ├── Task 4.2: Build "Confirm Delivery & Release Funds" flow with 5-star review modal.
 ├── Task 4.3: Build "Raise Dispute" wizard modal with photo evidence uploader.
 └── Task 4.4: Rebuild Sell Item wizard (/dashboard/listings/create) with live fee calculator.

Sprint 5: CMS Policy Pages, Blog & QA Testing (Days 15–17)
 ├── Task 5.1: Verify PageSeeder populates all 22 policy pages in the database.
 ├── Task 5.2: Style pages/show.blade.php with clean typography and documentation sidebar.
 ├── Task 5.3: Verify all dynamic admin settings link cleanly to frontend elements.
 └── Task 5.4: Run cross-browser and mobile responsive test sweeps.
```

---

*This document represents the complete, uncompromising frontend specification for X-Buy. Every screen, button, modal, and link has been accounted for to deliver a world-class Mercari-tier marketplace.*
