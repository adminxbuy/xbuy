<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'cards' => 'array',
        ];
    }

    protected $appends = ['buyer_badge_label', 'is_seller', 'is_buyer', 'is_hybrid', 'is_admin', 'admin_role', 'display_avatar_url', 'profile_id'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($user) {
            if ($user->role !== 'admin') {
                $hasBuyer = !empty($user->city) && !empty($user->state) && !empty($user->pincode);
                $hasSeller = \App\Models\SellerProfile::where('user_id', $user->id)->where('status', 'active')->exists();

                $newRole = 'buyer';
                if ($hasBuyer && $hasSeller) {
                    $newRole = 'hybrid';
                } elseif ($hasSeller) {
                    $newRole = 'seller';
                } elseif ($hasBuyer) {
                    $newRole = 'buyer';
                }

                if ($user->role !== $newRole) {
                    $user->role = $newRole;
                    $user->saveQuietly();
                }
            }
        });
    }

    // Relationships
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function getWalletInstance()
    {
        return $this->wallet()->firstOrCreate([], ['balance' => 0.00]);
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function staffEarnings()
    {
        return $this->hasMany(StaffEarning::class, 'staff_user_id');
    }

    public function staffPayrollNotices()
    {
        return $this->hasMany(StaffPayrollNotice::class, 'staff_user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Accessors
    public function getBuyerBadgeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->buyer_badge));
    }

    public function getIsSellerAttribute()
    {
        return in_array($this->role, ['seller', 'hybrid']);
    }

    public function getIsBuyerAttribute()
    {
        return in_array($this->role, ['buyer', 'hybrid']);
    }

    public function getIsHybridAttribute()
    {
        return $this->role === 'hybrid';
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Resolved admin_role — falls back to super_admin for legacy admins with null admin_role.
     */
    public function getAdminRoleAttribute(): string
    {
        if ($this->role !== 'admin') return '';
        return $this->attributes['admin_role'] ?? 'super_admin';
    }

    // ─── Role Helpers ────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->is_admin && $this->admin_role === 'super_admin';
    }

    public function isOperations(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super_admin', 'operations']);
    }

    public function isSupport(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super_admin', 'support', 'operations']);
    }

    public function isFinance(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super_admin', 'finance']);
    }

    public function isContent(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super_admin', 'content']);
    }

    public function isModerator(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super_admin', 'moderator', 'operations']);
    }

    /**
     * Central access gate — used in routes, sidebar, controllers.
     */
    public function canAccess(string $section): bool
    {
        if (!$this->is_admin) return false;
        if ($this->isSuperAdmin()) return true;

        $presets = ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator'];
        if (!in_array($this->admin_role, $presets)) {
            if ($section === 'dashboard') return true;
            $perm = $this->permissions[$section] ?? 'none';
            return $perm !== 'none';
        }

        return match($section) {
            'dashboard'   => true,
            'sellers'     => $this->isOperations() || $this->isModerator(),
            'listings'    => $this->isOperations() || $this->isModerator(),
            'orders'      => $this->isOperations() || $this->isSupport(),
            'disputes'    => $this->isOperations(),
            'escrow'      => $this->isFinance(),
            'payouts'     => $this->isFinance(),
            'tickets'     => $this->isSupport(),
            'analytics'   => $this->isFinance(),
            'users'       => $this->isOperations(),
            'fraud_flags' => $this->isModerator(),
            'ratings'     => $this->isModerator(),
            'alerts'      => $this->isModerator(),
            'categories'  => $this->isContent(),
            'articles'    => $this->isContent(),
            'pages'       => $this->isContent(),
            'mails'       => $this->isContent(),
            'settings'    => false, // super_admin only (handled above)
            'audit_logs'  => false,
            'admin_mgmt'  => false,
            default       => false,
        };
    }

    public function getDisplayAvatarUrlAttribute()
    {
        return $this->avatar ? url('storage/' . $this->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    public function getProfileIdAttribute()
    {
        return ($this->id * 1234567) + 3157000000;
    }
}
