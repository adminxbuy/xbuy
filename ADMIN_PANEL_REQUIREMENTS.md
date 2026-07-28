# X-Buy Admin Panel - Complete Requirements

## Tech Stack
- **Backend:** Laravel (PHP) - controllers, models, migrations, auth
- **Frontend:** Laravel Blade templates
- **CSS:** Tailwind CSS with CSS custom properties (CSS variables) for theming
- **Interactivity:** Alpine.js (for dropdowns, modals, tabs, dynamic UI)
- **Icons:** Lucide Icons (via CDN or Blade SVG)
- **Charts:** Chart.js (via CDN)
- **Rich Text Editor:** TinyMCE (via CDN, for articles, pages, content)
- **Date Picker:** Flatpickr (via CDN)
- **Drag & Drop:** SortableJS (via CDN, for category/page reorder)
- **File Upload:** Standard HTML file inputs with Laravel Storage
- **Auth:** Laravel session-based authentication (no JWT)
- **Hosting:** Hostinger (shared hosting or VPS with PHP + MySQL)
- **Database:** MySQL

---

## Project Structure

```
admin-panel/
├── app/
│   ├── Http/Controllers/Admin/    ← All admin controllers
│   ├── Models/                    ← Eloquent models
│   └── Http/Middleware/           ← Admin auth + role middleware
├── resources/views/
│   ├── layouts/admin.blade.php   ← Main layout (sidebar + header + main)
│   └── admin/                    ← All admin blade pages
├── routes/web.php                ← Admin routes
├── tailwind.config.js            ← Tailwind config with CSS variable tokens
└── public/build/                 ← Compiled CSS/JS
```

### Blade Layout Pattern:
Every admin page extends `layouts.admin.blade.php`:
```blade
@extends('layouts.admin')

@section('title', 'Page Title')

@section('content')
    {{-- Page content here --}}
@endsection
```

The layout provides: sidebar, header, flash messages, command palette, theme customizer. Individual pages only provide their own content.

---

## Role-Based Access Control

| Role | Can Access |
|------|-----------|
| `super_admin` | Everything - full access |
| `operations` | Sellers (full), Listings (full), Orders (full), Disputes (full), Users (full) |
| `support` | Orders (read-only), Support Tickets (full) |
| `finance` | Escrow (full), Payouts (full), Analytics (full) |
| `content` | Categories, Spec Templates, Policy Pages, Articles, Mail, Content Manager |
| `moderator` | Fraud Flags, Ratings, Alerts, Listings (write) |
| `custom` | Admin sets granular per-section permissions: view / edit / delete / all |

**Permission check in Blade:**
```blade
@if(Auth::user()->canAccess('sellers'))
    <a href="{{ route('admin.sellers') }}">Sellers</a>
@endif
```

**Middleware in routes:**
```php
Route::middleware(['auth', 'admin', 'admin.role:super_admin,operations'])
    ->prefix('admin')
    ->group(function () {
        // sellers, listings, orders routes
    });
```

---

## Theme & Design System

The entire admin panel uses CSS custom properties (CSS variables) for theming. Define all colors as CSS variables in the layout `<style>` block. No hardcoded colors in any Blade template.

### Color Tokens (use these Tailwind classes everywhere):
- **Main text:** `text-foreground`
- **Secondary text:** `text-muted-foreground`
- **Text on primary buttons:** `text-primary-foreground`
- **Card background:** `bg-card`
- **Muted background:** `bg-muted`
- **Borders:** `border-border`
- **Input borders:** `border-input`
- **Primary button bg:** `bg-primary`
- **Destructive button bg:** `bg-destructive`

### CSS Variables - Light Mode:
```css
:root {
    --background: #ffffff;
    --foreground: #09090b;
    --card: #ffffff;
    --card-foreground: #09090b;
    --muted: #f4f4f5;
    --muted-foreground: #71717a;
    --border: #e4e4e7;
    --input: #e4e4e7;
    --ring: #a1a1aa;
    --primary: #09090b;
    --primary-foreground: #ffffff;
    --destructive: #ef4444;
    --destructive-foreground: #ffffff;
}
```

### CSS Variables - Dark Mode:
```css
.dark {
    --background: #09090b;
    --foreground: #fafafa;
    --card: #0a0a0c;
    --card-foreground: #fafafa;
    --muted: #27272a;
    --muted-foreground: #a1a1aa;
    --border: #27272a;
    --input: #27272a;
    --ring: #52525b;
    --primary: #fafafa;
    --primary-foreground: #09090b;
    --destructive: #f87171;
    --destructive-foreground: #09090b;
}
```

### Dark Mode Toggle:
```html
<body :class="{ 'dark': isDark }" x-data="{ isDark: localStorage.getItem('darkMode') === 'true' }">
```

### Fonts:
- Body: Inter or Plus Jakarta Sans (via Google Fonts)
- Headings: Same family, bolder weights

### Border Radius:
- Cards: `rounded-xl`
- Buttons/Inputs/Badges: `rounded-md`
- Modals: `rounded-lg`

---

## Reusable Blade Component Patterns

### Page Header (use on EVERY page):
```blade
<div class="flex flex-wrap items-end justify-between gap-2 mb-6">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Page Title</h2>
        <p class="text-sm text-muted-foreground">Description of what this page does.</p>
    </div>
    <div class="flex items-center gap-2">
        {{-- action buttons here --}}
    </div>
</div>
```

### Button Classes:
- **Primary:** `inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground shadow-xs hover:bg-primary/90 h-9 px-4 transition-all disabled:pointer-events-none disabled:opacity-50`
- **Outline:** `inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground h-9 px-4 transition-all`
- **Small outline:** same but `h-8 px-3`
- **Ghost icon:** `inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium hover:bg-accent hover:text-accent-foreground h-9 w-9 transition-all`
- **Destructive:** `inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-destructive text-white shadow-xs hover:bg-destructive/90 h-9 px-4 transition-all disabled:pointer-events-none disabled:opacity-50`

### Card:
```blade
<div class="rounded-xl border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-row items-center justify-between space-y-0 pb-2 p-6">
        <h3 class="text-sm font-medium">Label</h3>
        <i data-lucide="icon-name" class="h-4 w-4 text-muted-foreground"></i>
    </div>
    <div class="px-6 pb-6">
        <div class="text-2xl font-bold">Value</div>
        <p class="text-xs text-muted-foreground">Description or change %</p>
    </div>
</div>
```

### Table:
```blade
<div class="overflow-hidden rounded-md border">
    <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors">
                <th class="h-10 px-2 text-start align-middle font-medium whitespace-nowrap text-muted-foreground">Header</th>
            </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
            <tr class="border-b transition-colors hover:bg-muted/50">
                <td class="p-2 align-middle">Cell</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Input:
```blade
<input type="text" name="field"
    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none"
    placeholder="Enter value...">
```

### Select:
```blade
<select name="field"
    class="flex h-9 items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none">
    <option value="">All</option>
    <option value="1">Option 1</option>
</select>
```

### Badge:
```blade
<span class="inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap {{ $class }}">
    {{ $label }}
</span>
```
Color variants as Tailwind classes:
- Default: `border-transparent bg-primary text-primary-foreground`
- Secondary: `border-transparent bg-secondary text-secondary-foreground`
- Destructive: `border-transparent bg-destructive text-white`
- Outline: `text-foreground`
- Success: `border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300`
- Warning: `border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300`
- Info: `border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300`

### Tab Bar (using Alpine.js):
```blade
<div class="flex items-center gap-1 rounded-lg bg-muted p-1" x-data="{ activeTab: '{{ $defaultTab ?? 'all' }}' }">
    @foreach($tabs as $key => $label)
        <button @click="activeTab = '{{ $key }}'"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all"
            :class="activeTab === '{{ $key }}' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
            {{ $label }}
            @if(isset($counts[$key]))
                <span class="ml-1.5 text-xs bg-muted-foreground/20 rounded-full px-1.5 py-0.5">{{ $counts[$key] }}</span>
            @endif
        </button>
    @endforeach
</div>
```

### Form Item:
```blade
<div class="grid gap-2">
    <label for="field" class="text-sm font-medium leading-none">Label</label>
    <input type="text" id="field" name="field"
        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
        value="{{ old('field', $value ?? '') }}">
    @error('field')
        <p class="text-sm text-destructive">{{ $message }}</p>
    @enderror
</div>
```

### Flash Message:
```blade
@if(session('success'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 flex items-center justify-between text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
        <div class="flex items-center gap-2">
            <i data-lucide="check-circle" class="h-4 w-4 text-emerald-500"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button @click="show = false"><i data-lucide="x" class="h-4 w-4"></i></button>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 flex items-center justify-between text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
         x-data="{ show: true }" x-show="show">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="h-4 w-4 text-destructive"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        <button @click="show = false"><i data-lucide="x" class="h-4 w-4"></i></button>
    </div>
@endif
```

### Confirmation Modal (Alpine.js):
```blade
<div x-data="{ open: false }">
    <button @click="open = true" class="...">Delete</button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-card border rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold">Are you sure?</h3>
            <p class="text-sm text-muted-foreground mt-2">This action cannot be undone.</p>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="open = false" class="...outline button classes...">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="...destructive button classes...">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Pagination (Laravel built-in):
```blade
<div class="flex items-center justify-between px-2 py-4">
    <p class="text-sm text-muted-foreground">
        Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} results
    </p>
    {{ $items->links() }}
</div>
```

---

## Layout Structure

### Sidebar (in admin.blade.php):
- Fixed left sidebar, width: w-64 (256px)
- Collapsible to icon-only mode (w-20) via Alpine.js toggle
- Sections with group labels: `<p class="text-[10px] font-medium uppercase tracking-widest px-3">Section Name</p>`
- Nav items: `<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors">`
- Active state: inverted colors (bg-primary text-primary-foreground)
- Inactive state: text-muted-foreground, hover:bg-muted hover:text-foreground
- Badge counts on certain items (pending KYC, open disputes, etc.)
- User info at bottom: avatar (ui-avatars.com), name, role, logout button

### Header (in admin.blade.php):
- Sticky top, height: h-14
- Left: sidebar toggle, vertical separator, search button (Cmd+K)
- Right: homepage link, notifications bell (dropdown with Alpine.js), dark mode toggle, theme customizer (dropdown), profile dropdown
- All dropdowns use Alpine.js x-data="{ open: false }" pattern

### Main Content (in admin.blade.php):
- padding: p-6
- max-width: max-w-[1600px] (full) or max-w-4xl (centered) based on layout setting
- `@yield('content')` renders page content

### Theme Customizer:
Stored in localStorage via Alpine.js:
- `themePreset`: default/violet/emerald/blue/rose (changes primary color)
- `themeScale`: xs/default/lg (changes font size)
- `themeRadius`: none/sm/md/lg/xl (changes border radius)
- `isDark`: true/false (dark mode toggle)
- `layoutMode`: full/centered
- `sidebarMode`: default/icon (collapsed)

---

## Global Features

### Command Palette (Cmd+K):
Alpine.js powered overlay. Triggered by Cmd+K keyboard shortcut or clicking search button in header.
```blade
<div x-show="searchOpen" class="fixed inset-0 z-[100] flex items-start justify-center pt-[20vh]">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="searchOpen = false"></div>
    <div class="relative w-full max-w-lg rounded-xl bg-card border shadow-2xl">
        <input type="text" placeholder="Type a command or search..." class="..." x-ref="searchInput">
        <div class="max-h-80 overflow-y-auto p-2">
            {{-- Navigation links --}}
        </div>
    </div>
</div>
```

### Notifications Dropdown:
Alpine.js dropdown in header. Shows bell icon with unread count badge. Dropdown shows recent 5 unread alerts.

### Profile Dropdown:
Alpine.js dropdown with avatar, name, email, Account link, Notifications link, Logout button (POST form with @csrf).

### Date Range Picker:
Alpine.js component with Flatpickr. Button trigger → popover with preset buttons (Today, Yesterday, 7 Days, etc.) + inline calendar. Submits parent form on date selection.

### Bulk Actions:
Alpine.js tracks selected row IDs via x-model checkboxes. Floating toolbar appears at bottom when rows selected. Form submits selected IDs as JSON.

### Flash Messages:
Auto-dismiss after 5 seconds via Alpine.js setTimeout.

---

## Page-by-Page Specifications

---

### 1. LOGIN PAGE

**URL:** `/admin/login`
**Layout:** Standalone (no sidebar/header) - custom centered layout

**Components:**
- Platform logo (centered)
- Platform name text
- "Admin Panel" subtitle
- Email input
- Password input
- Remember me checkbox
- "Sign In" primary button (full width)
- Error message area (shows below form on failure via @error directive)

**Behavior:**
- Standard Laravel form POST to `/admin/login`
- Laravel session-based auth (no JWT)
- On success: redirect to `/admin/dashboard`
- On failure: redirect back with errors

---

### 2. DASHBOARD

**URL:** `/admin/dashboard`
**Controller:** DashboardController@index

**Page Header:** Title "Dashboard", no description

**Section 1 - Stat Cards (grid: grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4):**
6 cards using the Card pattern above:
1. Total Sales (₹) - icon: trending-up
2. Escrow Held (₹) - icon: shield
3. Pending Listings - icon: package
4. Open Disputes - icon: alert-triangle
5. Monthly Revenue (₹) - icon: dollar-sign
6. Total Users - icon: users

Each card shows: icon, label, value ($stats array), percentage change

**Section 2 - Charts (grid-cols-1 lg:grid-cols-7 gap-4):**
- Left (lg:col-span-4): Revenue Trend line chart (Chart.js canvas)
- Right (lg:col-span-3): Orders by Category doughnut chart (Chart.js canvas)

**Section 3 - Tables (grid-cols-1 lg:grid-cols-7 gap-4):**
- Left (lg:col-span-4): Recent Disputes table + Recent Orders table
- Right (lg:col-span-3): Pending Seller Applications table + Top Sellers by GMV table

**Section 4 - Full width:**
- Recent Support Tickets table

**Filters:** Date range picker (Flatpickr) at top right, submits form

---

### 3. SALES OVERVIEW

**URL:** `/admin/sales-overview`
**Controller:** DashboardController@salesOverview

**Page Header:** Title "Sales Overview", description "Detailed breakdown of sales performance."

**Section 1 - Metric Cards (grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4):**
1. Total GMV with change %
2. Escrow Held with change %
3. Revenue (Commission) with change %
4. Fulfillment Rate with change %

**Section 2 - Charts (lg:grid-cols-7 gap-4):**
- Left (lg:col-span-4): GMV + Revenue dual line chart
- Right (lg:col-span-3): Category distribution + Escrow allocation pie chart

**Section 3 - Tables (lg:grid-cols-7 gap-4):**
- Left: Top Shops table (5 rows)
- Right: Upcoming Releases table + Recent Transactions table

**Filters:** Preset date range (Alpine.js segmented control) + Flatpickr custom range + Export CSV button

---

### 4. SELLERS - LISTING PAGE

**URL:** `/admin/sellers`
**Controller:** DashboardController@sellers

**Page Header:** Title "Sellers", description "Manage seller accounts, KYC verification and performance."

**Tab Bar:** All | Pending KYC (count) | Active (count) | Suspended | Banned
Tabs filter via query parameter: `?tab=active`

**Toolbar:** Search input (GET form) + KYC Status filter (select) + Badge Level filter (select) + Reset button + Trash button (opens Alpine.js modal)

**Table:** Uses the standard Table pattern
Columns: Checkbox, Shop Name (+ logo + owner name), Email, Phone, KYC Status (badge), Badge (badge), Status (badge), Joined, Actions (Alpine.js dropdown: View, Verify KYC, Suspend, Ban)

**Bulk Actions:** Alpine.js x-model checkboxes, floating bottom toolbar with bulk action form

**Pagination:** Laravel paginator

**Trash Modal:** Alpine.js modal, loads trashed sellers, shows Restore (POST with @method('POST')) and Permanent Delete (DELETE form) buttons

---

### 5. SELLERS - DETAIL PAGE

**URL:** `/admin/sellers/{id}`
**Controller:** DashboardController@sellerShow

**Page Header:** Back link + Shop Name + Owner name + Action buttons (Update Status, Update Badge)

**Section 1 - Profile Card:** Shop logo, name, owner, email, phone, status badges, KYC info, Razorpay account

**Section 2 - Metrics Cards (grid-cols-4):** Total GMV, Total Orders, Average Rating, Active Listings

**Section 3 - KYC Management:** Approve/Reject buttons with modal for reason

**Section 4 - Rating Breakdown:** Progress bars for item_accuracy, packaging, shipping_speed, communication

**Section 5 - Tabs (Alpine.js):**
- Tab: Orders → table
- Tab: Listings → table
- Tab: Messages → chat interface (messages list + send form via AJAX POST)

---

### 6. LISTINGS - LISTING PAGE

**URL:** `/admin/listings`
**Controller:** DashboardController@listings

**Page Header:** Title "Listings", description "Review, approve and manage product listings."

**Tab Bar:** All (count) | Pending Approval (count) | Active (count) | Paused | Rejected | Sold

**Toolbar:** Search input + Date range picker + Trash button

**Table Columns:** Checkbox, Product (thumbnail + title + brand), Price, Category, Seller, Status (badge), Created, Actions (View, Approve, Reject, Pause)

**Bulk Actions + Pagination + Trash Modal:** Same pattern as sellers

---

### 7. LISTINGS - DETAIL PAGE

**URL:** `/admin/listings/{id}`
**Controller:** DashboardController@listingShow

**Page Header:** Back link + Product Title + Action buttons (Approve, Reject, Pause)

**Section 1 - Product Info Card:** Image gallery, title, description, brand, price, category, serial number, condition, status

**Section 2 - Seller Info Card:** Shop name, owner, contact (links to seller detail)

**Section 3 - Actions Card:** Approve button (form POST), Reject button (Alpine.js modal with reason textarea), Pause button (form POST)

---

### 8. ORDERS - LISTING PAGE

**URL:** `/admin/orders`
**Controller:** DashboardController@orders

**Page Header:** Title "Orders", description "Manage all orders, shipments and buyer communications."

**Tab Bar:** All (count) | Pending Payment | Pending Shipment | Shipped | Delivered | Completed | Disputed | Cancelled | Refunded (each with count)

**Toolbar:** Search input + Status filter (select) + Date range picker + Trash button

**Table Columns:** Checkbox, Order# (monospace), Listing (thumbnail + title), Buyer, Seller, Amount, Status (badge), Payment (badge), Created, Actions (View, Update Status, Download Invoice)

**Side Drawer:** Alpine.js slide-in panel from right, triggered by View action. Shows order quick-view with mini timeline and action buttons.

**Bulk Actions + Pagination + Trash Modal**

---

### 9. ORDERS - DETAIL PAGE

**URL:** `/admin/orders/{id}`
**Controller:** DashboardController@orderShow

**Page Header:** Back link + "Order #X" + Status badge + Actions (Update Status dropdown, Download Invoice, Resend Invoice)

**Section 1 - Timeline (vertical):** Step indicators using colored circles + connecting lines
1. Payment Captured → 2. Shipment Confirmed → 3. In Transit → 4. Delivered → 5. Completed & Released

**Section 2 - Info Cards (2-col):** Order Details (amounts breakdown) + People (buyer + seller info)

**Section 3 - Payment Details Card:** Razorpay ID, amount, escrow status

**Section 4 - Shipment Details Card:** Carrier, AWB, tracking (if exists)

**Section 5 - Dispute Card:** Link to dispute detail (if exists)

**Section 6 - Rating Card:** Star breakdown (if exists)

**Section 7 - Messages Card:** Chat interface (load via AJAX, send via AJAX POST)

---

### 10. DISPUTES - LISTING PAGE

**URL:** `/admin/disputes`
**Controller:** DashboardController@disputes

**Page Header:** Title "Disputes", description "Review and resolve buyer-seller disputes."

**Tab Bar:** All | Open | Seller Responded | Under Review | Resolved | Rejected

**Toolbar:** Status filter + Date range picker + Trash button

**Table Columns:** Checkbox, Dispute#, Order# (link), Buyer, Seller, Reason, Status (badge), Created, Actions (View)

**Bulk Actions + Pagination + Trash Modal**

---

### 11. DISPUTES - DETAIL PAGE

**URL:** `/admin/disputes/{id}`
**Controller:** DashboardController@disputeShow

**Page Header:** Back link + "Dispute #X" + Status badge + Actions (Resolve, Reopen)

**Section 1 - Dispute Info Card:** ID, Order#, Buyer, Seller, Reason, Status, Created

**Section 2 - Order Details Card:** Listing info, order amounts

**Section 3 - Evidence Card:** Uploaded attachments

**Section 4 - Conversation Thread:** Messages between buyer, seller, admin (chronological)

**Section 5 - Resolution Form (if not resolved):**
- Decision radios: Favor Buyer / Favor Seller / Partial Refund
- Refund Amount input (conditional)
- Fault Assignment radios
- Admin Notes textarea
- Resolve button (form POST)

**Section 6 - Resolution Details (if resolved):** Shows decision, amounts, fault, notes

---

### 12. USERS (BUYERS)

**URL:** `/admin/users`
**Controller:** DashboardController@users

**Page Header:** Title "Users", description "Manage buyer accounts and user activity."

**Tab Bar:** All (count) | New Buyers (count) | Verified (count) | Trusted (count) | Deleted (count)

**Toolbar:** Search input + Badge filter + Status filter + Date range picker

**Table Columns:** Checkbox, User (avatar + name + email), Phone, Badge (badge), Total Orders, Wallet Balance, Online Status (green dot), Actions (Suspend, Ban - each with modal for reason)

**Pagination**

---

### 13. ESCROW

**URL:** `/admin/escrow`
**Controller:** DashboardController@escrow

**Page Header:** Title "Escrow", description "Manage escrow payments, releases and refunds."

**Summary Cards (grid-cols-4):** Total Held, Due Today, Overdue, Total Disputed

**Tab Bar:** All | Held | Released | Refunded | Disputed

**Toolbar:** Search (seller shop name) + Status filter + Date range picker + Trash button

**Table Columns:** Checkbox, Order#, Buyer, Seller, Amount, Status (badge), Created, Due Date, Actions (Release, Refund, Partial Release with modal, Retry Payout)

**Bulk Actions + Pagination + Trash Modal**

---

### 14. PAYOUTS

**URL:** `/admin/payouts`
**Controller:** DashboardController@payouts

**Page Header:** Title "Payouts", description "Track all seller payouts and commission earnings."

**Summary Cards (grid-cols-3):** Released This Month, Total Commission, Average Payout

**Toolbar:** Search + Date range picker + Export CSV button + Trash button

**Table Columns:** Checkbox, Order#, Seller, Gross Amount, Commission, Net Payout, Released At, Actions (View)

**Pagination + Trash Modal**

---

### 15. SUPPORT TICKETS

**URL:** `/admin/tickets`
**Controller:** (use DashboardController or dedicated controller)

**Page Header:** Title "Support Tickets", description "Manage customer support tickets."

**Tab Bar:** All | Open | In Progress | Resolved | Closed

**Toolbar:** Search input + Status filter

**Table Columns:** Ticket#, Subject, Buyer, Priority (badge), Status (badge), Created, Actions (View, Assign, Update Status)

**Pagination**

---

### 16. ARTICLES - LISTING PAGE

**URL:** `/admin/articles`
**Controller:** ArticleController@index

**Page Header:** Title "Articles", description "Create and manage blog articles." + Create Article button (primary, links to create page)

**Toolbar:** Search input + Status filter (Published/Draft) + Category filter + Trash button

**Table Columns:** Checkbox, Title (+ subtitle), Category (badge), Status (toggle via Alpine.js AJAX), Author, Created, Updated, Actions (Edit, Delete, Toggle Status)

**Bulk Actions + Pagination + Trash Modal**

---

### 17. ARTICLES - CREATE PAGE

**URL:** `/admin/articles/create`
**Controller:** ArticleController@create

**Page Header:** Back link + "Create Article"

**Form (card):**
- Title (text input, required)
- Subtitle (text input, optional)
- Category (text input)
- Featured Image (file input with preview)
- Content (TinyMCE rich text editor - textarea with class `tinymce-editor`)
- Status toggle: Publish now / Save as Draft (Alpine.js radio)

**Buttons:** Save Draft (outline, name="status" value="draft") + Publish (primary, name="status" value="published")

**Form method:** POST to store route, @csrf, @method('POST')

---

### 18. ARTICLES - EDIT PAGE

**URL:** `/admin/articles/{id}/edit`
**Controller:** ArticleController@edit

**Same as Create, pre-filled with old() values**
**Additional:** Delete button (destructive, confirmation modal with DELETE form)

**Form method:** PUT to update route, @csrf, @method('PUT')

---

### 19. RATINGS

**URL:** `/admin/ratings`
**Controller:** RatingController@index

**Page Header:** Title "Ratings", description "Manage customer ratings and reviews." + Add Rating button

**Stats Cards (grid-cols-5):** Average Rating, Total Reviews, 5-star/4-star/3-star/2-star/1-star counts with progress bars

**Toolbar:** Search + Seller filter + Buyer filter + Rating Type filter + Score Range filter + Trash button

**Table Columns:** Checkbox, Order#, Seller, Buyer, Review (truncated), Score, Type (badge), Date, Actions (Edit, Delete)

**Pagination + Trash Modal**

---

### 20. RATINGS - CREATE PAGE

**URL:** `/admin/ratings/create`
**Controller:** RatingController@create

**Page Header:** Back link + "Add Rating"

**Form:** Select Order (dropdown), Seller, Buyer, Rating fields (1-5 each), Review Text

**Form method:** POST

---

### 21. RATINGS - EDIT PAGE

**URL:** `/admin/ratings/{id}/edit`
**Controller:** RatingController@edit

**Same as Create, pre-filled**
**Additional:** Delete button

**Form method:** PUT

---

### 22. ALERTS

**URL:** `/admin/alerts`
**Controller:** DashboardController@alerts

**Page Header:** Title "Alerts", description "View and manage system alerts." + Mark All Read button (outline) + Clear All button (destructive outline)

**Toolbar:** Search + Status filter (Unread/Read) + Date range picker

**Table Columns:** ID, Title, Message, Status (badge), Created, Actions (Mark Read if unread)

**Pagination**

---

### 23. FRAUD FLAGS

**URL:** `/admin/fraud-flags`
**Controller:** DashboardController@fraudFlagsIndex

**Page Header:** Title "Fraud Flags", description "Monitor and review suspicious activity." + Create Manual Flag button

**Summary Cards (grid-cols-4):** Total Flags, Pending, Reviewed, Dismissed

**Tab Bar:** All | Pending | Reviewed | Dismissed

**Toolbar:** Search + Flag Type filter + Status filter + Date range picker + Trash button

**Table Columns:** ID, Flagged User, Flagged Listing, Flag Type (badge), Note, Reviewer, Status (badge), Created, Actions (Review, Dismiss, Delete)

**Create Manual Flag Modal (Alpine.js):** Flag Type (select), User (searchable select), Listing (optional), Note (textarea)

**Pagination + Trash Modal**

---

### 24. CATEGORIES

**URL:** `/admin/categories`
**Controller:** CategoryController@index

**Page Header:** Title "Categories", description "Organize product categories." + Add Category button

**Layout:** Sortable list (not table). Each row: drag handle (SortableJS), name, slug, sort order, active toggle (AJAX), children count, Actions (Edit modal, Delete confirmation, Add Subcategory)

**Add/Edit Category Modal (Alpine.js):** Name, Parent Category (select), Sort Order, Active toggle

**Reorder:** SortableJS drag-and-drop, saves order via AJAX PUT

---

### 25. SPEC TEMPLATES

**URL:** `/admin/spec-templates`
**Controller:** SpecTemplateController@index

**Page Header:** Title "Spec Templates", description "Define spec templates for categories." + Add Template button

**Layout:** Grouped by category. Each template shows fields as key-value chips + sort order + Actions

**Add/Edit Modal (Alpine.js):** Category (select), Dynamic key-value pairs (add/remove rows with Alpine.js), Sort Order

---

### 26. POLICY PAGES (CMS)

**URL:** `/admin/pages`
**Controller:** PageController@index

**Page Header:** Title "Pages", description "Manage policy pages." + Add Page button + Add Category button

**Layout:** Grouped by category, each page row: drag handle, title, category (badge), last edited by, updated date, Actions (Edit link, Delete confirmation)

**Add Category Modal:** Name input

**Reorder:** SortableJS

---

### 27. POLICY PAGES - EDIT PAGE

**URL:** `/admin/pages/{id}/edit`
**Controller:** PageController@edit

**Form (card):** Title, Category (select), Content (TinyMCE)

**Buttons:** Cancel + Save + Delete

**Form method:** PUT

---

### 28. MAIL MANAGEMENT

**URL:** `/admin/mails`
**Controller:** MailManagementController@index

**Page Header:** Title "Mail Management", description "Manage subscribers, campaigns and email history."

**Tab Bar:** Subscribers | Campaigns | History (Alpine.js toggles content sections)

**Tab: Subscribers:**
- Toolbar: Search + Status filter + Source filter + Date range
- Table: Checkbox, Email, Name, Source, Status (badge), Subscribed Date, Actions (Delete)
- Bulk actions bar
- Add Subscriber form/modal
- Import Users button (modal: paste emails textarea)
- Pagination

**Tab: Campaigns:**
- Table: Subject, Target Group, Status, Sent Date
- Send Campaign button (modal: subject, body TinyMCE, target group, type, schedule date)

**Tab: History:**
- Search + Status filter
- Table: To, Subject, Template, Status (badge), Sent At, Actions (Resend, View)
- Pagination

---

### 29. CONTENT MANAGER

**URL:** `/admin/content`
**Controller:** ContentManagerController@index

**Page Header:** Title "Content Manager", description "Upload and manage media files." + Upload button

**Tab Bar:** Images | PDFs | Videos

**Grid View:** Each file as a card with thumbnail, name, size, date, Delete + Copy URL buttons

**Upload Modal:** File input with type selector, progress indication

**Trash Section:** Deleted files with Restore and Permanent Delete

---

### 30. ADMIN ACCOUNTS

**URL:** `/admin/admin-accounts`
**Controller:** AdminAccountController@index

**Page Header:** Title "Admin Accounts", description "Manage admin users." + Create Admin button

**Tab Bar:** All (count) | Super Admin | Operations | Support | Finance | Content | Moderator | Custom

**Toolbar:** Search + Role filter

**Table Columns:** Admin (avatar + name + email), Role (badge), Last Login, Status, Actions (Edit, Delete)

**Pagination**

---

### 31. ADMIN ACCOUNTS - CREATE

**URL:** `/admin/admin-accounts/create`
**Controller:** AdminAccountController@create

**Form (card):** Name, Email (with AJAX uniqueness check), Password, Confirm Password, Role (select)

**If Role = Custom:** Permissions grid (table with checkboxes per section: view/edit/delete/all)

**Form method:** POST

---

### 32. ADMIN ACCOUNTS - EDIT

**URL:** `/admin/admin-accounts/{id}/edit`
**Controller:** AdminAccountController@edit

**Same as Create, pre-filled, password optional**
**Additional:** Delete Account button (confirmation modal, type name to confirm)

**Form method:** PUT

---

### 33. STAFF DIRECTORY

**URL:** `/admin/staff`
**Controller:** StaffPayrollController@staffDirectory

**Page Header:** Title "Staff Directory", description "Manage staff members."

**Toolbar:** Search

**Table Columns:** Staff (avatar + name + email), Position/Dept, This Month Earnings, Total Earnings, Status, Actions (View Profile, Send Notice, Suspend, Terminate)

---

### 34. STAFF - PROFILE PAGE

**URL:** `/admin/staff/{id}`
**Controller:** StaffPayrollController@staffProfile

**Page Header:** Back link + Staff Name + Position + Actions (Send Notice, Suspend/Terminate/Reactivate)

**Section 1 - Profile Card**

**Section 2 - Monthly Earnings Summary:** Cards/table grouped by month with expandable order details

**Section 3 - Notices Tab:** List of notices with type, subject, message, date

**Section 4 - Activity Logs Tab:** Table of admin actions

---

### 35. STAFF - SEND NOTICE

**URL:** `/admin/staff/{id}/notice`
**Controller:** StaffPayrollController@sendNoticeForm

**Form (card):** Type (select), Subject, Message (textarea), Delivery Method (radio), PDF Upload (optional)

**Form method:** POST

---

### 36. PAYROLL OVERVIEW

**URL:** `/admin/payroll`
**Controller:** StaffPayrollController@payrollOverview

**Page Header:** Title "Payroll", description "Staff payroll overview."

**Summary Cards (grid-cols-4):** Total Pending, Staff Count, Orders This Month, Trigger Threshold + Progress bar

**Pending Disbursements Table:** Staff Name, Orders, Gross, Expenses, Net, Status
**Disburse All button** (confirmation modal)

**Link to Payroll Settings**

---

### 37. PAYROLL SETTINGS

**URL:** `/admin/payroll/settings`
**Controller:** StaffPayrollController@payrollSettings

**Form (card):** Commission %, Platform Expense %, Min Cap, Max Cap, Payment Day, Amendment Trigger Orders

**Form method:** POST

---

### 38. MY EARNINGS

**URL:** `/admin/my-earnings`
**Controller:** MyEarningsController@index

**Page Header:** Title "My Earnings", description "View your earnings history."

**Summary Cards (grid-cols-3):** This Month, Last Month, Total Earned

**Month picker** (Flatpickr month input)

**Orders Table:** Order#, Listing Title, Order Amount, Your Earning + total row

**Salary Slips section:** List with download/print links

---

### 39. ANALYTICS

**URL:** `/admin/analytics`
**Controller:** DashboardController@analyticsPage

**Page Header:** Title "Analytics", description "In-depth analytics and metrics."

**Period Selector:** Tabs: 7 Days | 30 Days | 90 Days | Custom (date range picker)

**Metrics Cards (grid-cols-4):** Total GMV, Total Orders, Total Commission, AOV (each with change %)

**Charts (2-col):** GMV Trend line chart + Dispute Rate Trend line chart

**Charts (2-col):** Category Breakdown doughnut + Order Status doughnut

**Tables (2-col):** Top 10 Sellers by GMV + Top 10 Sellers by Rating

---

### 40. SETTINGS

**URL:** `/admin/settings`
**Controller:** DashboardController@settings

**Page Header:** Title "Settings", description "Manage platform settings."

**Layout:** Sidebar nav (sticky left) + Content area (right) - Alpine.js tabs within page

**Sidebar Nav:** General | Notifications | SMTP/Email

**Tab: General:**
Form: Platform Name, Support Email, Logo upload, Favicon upload, Banner upload, Banner URL, Currency, Timezone
Save button (form POST)

**Tab: Notifications:**
Toggle switches (Alpine.js): Admin 2FA, Email Notifications, WhatsApp Notifications
Text inputs: Admin Email, WhatsApp Number
Save button

**Tab: SMTP:**
Form: Host, Port, Username, Password (show/hide toggle), Encryption, From Address, From Name
Buttons: Test Connection (AJAX POST, shows toast) + Save (form POST)

---

### 41. AUDIT LOGS

**URL:** `/admin/audit-logs`
**Controller:** DashboardController@auditLogs

**Page Header:** Title "Audit Logs", description "Track all admin actions."

**Table (read-only):** Timestamp, Admin (name + avatar), Action (badge), Description, IP Address

**Pagination** (20 per page, no filtering)

---

### 42. PROFILE

**URL:** `/admin/profile`
**Controller:** DashboardController@profile

**Page Header:** Title "Profile", description "Manage your account."

**Profile Card:** Avatar (upload), Name, Email (disabled), Phone, Current Password, New Password
Update button (form POST)

**Change Requests Card:** List of pending profile change requests

---

### 43. MY EARNINGS - PRINT VIEW

**URL:** `/admin/my-earnings/print`
**Controller:** MyEarningsController@print

**Layout:** Clean printable layout (no sidebar/header - separate minimal layout)
**Content:** Earnings summary + orders table for specific month

---

### 44. TRASH PAGE

**URL:** `/admin/trash/{model}`
**Controller:** DashboardController@viewTrash

**Page Header:** Back link + "Trashed [Model Name]"

**Search:** Search input

**Table:** Name/Title, Deleted At, Actions (Restore button - POST form, Permanent Delete - DELETE form with confirmation)

**Pagination**

**Supported models:** sellers, listings, orders, disputes, escrow, payouts, tickets, articles, subscribers, ratings, fraud-flags, alerts, pages, page-categories, categories, content

---

## Data Models (Eloquent)

### User
```
id, name, email, phone, password, role (buyer|seller|admin|staff|hybrid),
is_admin (boolean), is_staff (boolean), admin_role (string),
custom_permissions (JSON), wallet_balance (decimal), badge (string),
status (string), remember_token, created_at, updated_at
```
**Relationships:** sellerProfile, orders (as buyer), staffProfile

### SellerProfile
```
id, user_id (FK→users), shop_name, shop_description, logo (string/path),
kyc_status (pending|approved|rejected), kyc_documents (JSON),
badge_level (basic|verified|fulfilled), status (active|suspended|banned),
visit_verified (boolean), razorpay_account_id (string),
created_at, updated_at
```
**Relationships:** user, listings, orders

### Listing
```
id, seller_id (FK→seller_profiles), title, description, brand, price (decimal),
category (string), serial_number, condition,
listing_status (pending_approval|active|paused|rejected|sold),
images (JSON - array of paths), created_at, updated_at
```
**Relationships:** seller, orders

### Order
```
id, order_number (unique), listing_id (FK), buyer_id (FK→users),
seller_id (FK→seller_profiles), amount (decimal), commission (decimal),
status (pending_payment|pending_shipment|shipment_confirmed|in_transit|delivered|completed|disputed|cancelled|refunded|partially_refunded),
payment_status (captured|pending|failed), razorpay_payment_id,
created_at, updated_at
```
**Relationships:** listing, buyer, seller, escrow, dispute, rating, buyerMessages

### Escrow
```
id, order_id (FK), amount (decimal), status (held|released|refunded|partially_released|disputed),
seller_amount (decimal), platform_commission (decimal),
released_at (timestamp), created_at, updated_at
```
**Relationships:** order

### Dispute
```
id, order_id (FK), buyer_id (FK→users), seller_id (FK→seller_profiles),
reason (text), status (open|seller_responded|under_review|resolved|rejected),
decision (string), refund_amount (decimal), fault (string), admin_notes (text),
created_at, updated_at
```
**Relationships:** order, buyer, seller, responses

### SupportTicket
```
id, buyer_id (FK→users), subject, description (text), priority (low|medium|high),
status (open|in_progress|resolved|closed), created_at, updated_at
```
**Relationships:** buyer

### Rating
```
id, order_id (FK), seller_id (FK→seller_profiles), buyer_id (FK→users),
item_accuracy (tinyint 1-5), packaging (tinyint 1-5),
shipping_speed (tinyint 1-5), communication (tinyint 1-5),
weighted_total (decimal), review_text (text), rating_type (string),
created_at, updated_at
```
**Relationships:** order, seller, buyer

### FraudFlag
```
id, flagged_user_id (FK→users), flagged_listing_id (FK→listings, nullable),
flag_type (string), note (text), reviewer_id (FK→users, nullable),
status (pending|reviewed|dismissed), created_at, updated_at
```
**Relationships:** flaggedUser, flaggedListing, reviewer

### AdminAlert
```
id, user_id (FK→users, nullable), title, message (text),
is_read (boolean default 0), created_at
```

### Article
```
id, title, subtitle, category (string), content (longText),
featured_image (string/path), status (published|draft),
author_id (FK→users), created_at, updated_at
```
**Relationships:** author

### Category
```
id, name, slug (unique), parent_id (FK→categories, nullable),
sort_order (int default 0), is_active (boolean default 1),
created_at, updated_at
```
**Relationships:** parent, children

### SpecTemplate
```
id, category_id (FK→categories), fields (JSON - array of {key, value}),
sort_order (int default 0), created_at, updated_at
```
**Relationships:** category

### Page
```
id, title, slug (unique), content (longText),
category_id (FK→page_categories, nullable), sort_order (int default 0),
last_edited_by (FK→users, nullable), created_at, updated_at
```
**Relationships:** category, lastEditor

### PageCategory
```
id, name, slug, sort_order (int default 0), created_at, updated_at
```

### MailCampaign
```
id, subject, body (longText), target_group (string), type (string),
status (draft|scheduled|sent), scheduled_at (timestamp, nullable),
sent_at (timestamp, nullable), created_at
```

### MailSubscriber
```
id, email (unique), name, source (string),
status (active|unsubscribed), subscribed_at (timestamp), created_at
```

### EmailLog
```
id, to_email, subject, template_name, status (sent|failed),
sent_at (timestamp), created_at
```

### SiteSetting
```
id, key (unique), value (text, nullable), group (string),
created_at, updated_at
```

### StaffProfile
```
id, user_id (FK→users), position, department,
joined_at (date), status (active|suspended|terminated),
created_at, updated_at
```
**Relationships:** user

### StaffEarning
```
id, staff_id (FK→users), order_id (FK→orders),
final_earning (decimal), month_year (string YYYY-MM),
created_at
```
**Relationships:** staff, order

### StaffPayrollNotice
```
id, staff_id (FK→users), type (string), subject, message (text),
delivery_method (email|in_app|pdf), pdf_file (string/path, nullable),
created_at
```
**Relationships:** staff

### AdminActivityLog
```
id, admin_id (FK→users), action (string), description (text),
ip_address (string), created_at
```
**Relationships:** admin

### AdminSellerMessage
```
id, seller_id (FK→seller_profiles), sender_id (FK→users),
message (text), created_at
```
**Relationships:** sender

### AdminBuyerMessage
```
id, order_id (FK→orders), sender_id (FK→users),
message (text), created_at
```
**Relationships:** sender

---

## Important Implementation Notes

1. **Never use hardcoded colors.** Always use CSS variable tokens via Tailwind classes (text-foreground, bg-card, etc.). The only exception is `text-white` on semantic colored backgrounds (bg-emerald-500, bg-rose-500, etc.).

2. **Every page must use the same page header pattern** with title (text-2xl font-bold tracking-tight) and description (text-sm text-muted-foreground).

3. **All forms must use consistent input styling** (h-9, border-input, rounded-md, text-sm, shadow-xs).

4. **All tables must use consistent styling** (overflow-hidden rounded-md border wrapper, h-10 header cells, hover:bg-muted/50 rows).

5. **Dark mode must work everywhere.** Toggle via Alpine.js + localStorage. The `.dark` class on `<body>` activates dark CSS variables.

6. **All modals/confirmations use Alpine.js x-data="{ open: false }" pattern** with consistent styling (fixed inset-0, bg-black/50 backdrop, bg-card border rounded-lg content).

7. **Flash messages auto-dismiss after 5 seconds** via Alpine.js setTimeout.

8. **Responsive:** Sidebar collapses on mobile (hamburger menu). Tables get horizontal scroll. Grids go single column on small screens.

9. **Loading states:** Use Alpine.js to show skeleton placeholders while content loads.

10. **Empty states:** When a table has no data, show centered message: "No results found."

11. **Confirmation dialogs for ALL destructive actions** (delete, ban, suspend, etc.)

12. **All dates formatted consistently:** "d MMM yyyy" for dates, "d MMM yyyy, h:mm a" for timestamps, relative time where appropriate.

13. **Use @csrf and @method directives on ALL forms** for Laravel security.

14. **Use Laravel validation:** Return `back()->withErrors()` from controllers. Show errors via `@error` directive in Blade.

15. **Soft deletes on all major models** with force-delete after 30 days (via scheduled task).

16. **Activity logging:** Call `$this->logAction()` in controllers for all significant state changes.

17. **File uploads use Laravel Storage** with proper validation (mimes, max size). Store in `storage/app/public/` with symbolic link.

18. **CDN libraries** (Chart.js, TinyMCE, Flatpickr, SortableJS, Lucide Icons) loaded in the admin layout. No npm build needed for these.

19. **Tailwind CSS** compiled via `npm run build` (Vite). CSS variables defined in `resources/css/app.css` and Tailwind config.

20. **Alpine.js** loaded via CDN in the layout. All interactivity (dropdowns, modals, tabs, toggles) uses Alpine.js directives directly in Blade templates.
