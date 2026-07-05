<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    private function logAction(string $action, string $details): void
    {
        AdminActivityLog::create([
            'admin_id'    => Auth::id(),
            'action'      => $action,
            'description' => $details,
            'ip_address'  => request()->ip(),
        ]);
    }

    /**
     * List all admin accounts.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('admin_role')) {
            $role = $request->input('admin_role');
            $presets = ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator'];
            if ($role === 'custom') {
                $query->whereNotIn('admin_role', $presets);
            } else {
                $query->where('admin_role', $role);
            }
        }

        $admins = $query->paginate(20)->withQueryString();

        $rawCounts = User::where('role', 'admin')
            ->selectRaw('admin_role, count(*) as total')
            ->groupBy('admin_role')
            ->pluck('total', 'admin_role')
            ->toArray();

        $presets = ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator'];
        $roleCounts = [];
        $customTotal = 0;
        foreach ($rawCounts as $r => $tot) {
            if (in_array($r, $presets)) {
                $roleCounts[$r] = $tot;
            } else {
                $customTotal += $tot;
            }
        }
        $roleCounts['custom'] = $customTotal;

        return view('admin.admin-accounts.index', compact('admins', 'roleCounts'));
    }

    /**
     * Show the form to create a new staff account.
     */
    public function create()
    {
        return view('admin.admin-accounts.create');
    }

    /**
     * Show the form to edit an existing staff account.
     */
    public function edit(int $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        return view('admin.admin-accounts.edit', compact('admin'));
    }

    /**
     * Check if email exists for dynamic form UI feedback.
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        return response()->json([
            'exists' => $user !== null,
            'name'   => $user ? $user->name : null
        ]);
    }

    /**
     * Create a new admin account or promote an existing user.
     */
    public function store(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        $adminRole = $request->input('admin_role');
        $presets = ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator'];
        if ($adminRole === 'custom') {
            $adminRole = $request->input('custom_role_title') ?: 'custom';
        }

        if ($user) {
            // Existing user promotion
            $request->validate([
                'email'      => 'required|email',
                'admin_role' => 'required|string|max:50',
            ]);

            // Promote user to admin
            $user->role = 'admin';
            $user->admin_role = $adminRole;
            $user->permissions = !in_array($adminRole, $presets) ? ($request->input('permissions') ?? []) : null;
            $user->save();

            // Ensure StaffProfile exists and is active
            $profile = \App\Models\StaffProfile::firstOrNew(['user_id' => $user->id]);
            $profile->designation = $adminRole === 'super_admin' ? 'Superadmin Executive' : ucfirst(str_replace('_', ' ', $adminRole));
            $profile->status = 'active';
            if (!$profile->exists) {
                $profile->appointment_date = now()->toDateString();
                $profile->appointment_letter_ref = 'XBUY/APT/' . now()->year . '/' . sprintf('%03d', $user->id);
            }
            $profile->save();

            $this->logAction('admin_account_promote', "Promoted user to admin: {$user->email} with role: {$adminRole}");

            return redirect()->route('admin.accounts.index')
                ->with('success', "Existing user '{$user->name}' promoted to admin with role: " . ucfirst(str_replace('_', ' ', $adminRole)));
        } else {
            // New staff account direct creation
            $validated = $request->validate([
                'name'       => 'required|string|max:100',
                'email'      => 'required|email|unique:users,email',
                'password'   => 'required|string|min:8|confirmed',
                'admin_role' => 'required|string|max:50',
                'permissions'=> 'nullable|array',
            ]);

            $user = User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'phone'      => '98765' . rand(10000, 99999),
                'password'   => Hash::make($validated['password']),
                'role'       => 'admin',
                'admin_role' => $adminRole,
                'permissions'=> !in_array($adminRole, $presets) ? ($request->input('permissions') ?? []) : null,
                'status'     => 'active',
            ]);

            // Ensure StaffProfile exists and is active
            $profile = \App\Models\StaffProfile::firstOrNew(['user_id' => $user->id]);
            $profile->designation = $adminRole === 'super_admin' ? 'Superadmin Executive' : ucfirst(str_replace('_', ' ', $adminRole));
            $profile->status = 'active';
            if (!$profile->exists) {
                $profile->appointment_date = now()->toDateString();
                $profile->appointment_letter_ref = 'XBUY/APT/' . now()->year . '/' . sprintf('%03d', $user->id);
            }
            $profile->save();

            $this->logAction('admin_account_create', "Created admin account: {$user->email} with role: {$adminRole}");

            return redirect()->route('admin.accounts.index')
                ->with('success', "Admin account '{$user->name}' created with role: " . ucfirst(str_replace('_', ' ', $adminRole)));
        }
    }

    /**
     * Update admin role (and optionally reset password).
     */
    public function update(Request $request, int $id)
    {
        $target = User::where('role', 'admin')->findOrFail($id);

        // Prevent downgrading self
        if ($target->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email,' . $target->id,
            'admin_role'  => 'required|string|max:50',
            'password'    => 'nullable|string|min:8|confirmed',
            'permissions' => 'nullable|array',
        ]);

        $adminRole = $validated['admin_role'];
        $presets = ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator'];
        if ($adminRole === 'custom') {
            $adminRole = $request->input('custom_role_title') ?: 'custom';
        }

        $target->name = $validated['name'];
        $target->email = $validated['email'];
        $target->admin_role = $adminRole;
        $target->permissions = !in_array($adminRole, $presets) ? ($request->input('permissions') ?? []) : null;
        $target->is_staff = true;
        $target->staff_role = $adminRole === 'super_admin' ? 'superadmin' : 'admin';

        if (!empty($validated['password'])) {
            $target->password = Hash::make($validated['password']);
        }

        $target->saveQuietly();

        // Ensure StaffProfile exists and is active
        $profile = \App\Models\StaffProfile::firstOrNew(['user_id' => $target->id]);
        $profile->designation = $adminRole === 'super_admin' ? 'Superadmin Executive' : ucfirst(str_replace('_', ' ', $adminRole));
        $profile->status = 'active';
        if (!$profile->exists) {
            $profile->appointment_date = now()->toDateString();
            $profile->appointment_letter_ref = 'XBUY/APT/' . now()->year . '/' . sprintf('%03d', $target->id);
        }
        $profile->save();

        $this->logAction('admin_account_update', "Updated admin {$target->email}: role → {$adminRole}");

        return redirect()->route('admin.accounts.index')
            ->with('success', "Admin '{$target->name}' updated successfully.");
    }

    /**
     * Revoke admin access (set role back to buyer).
     */
    public function destroy(int $id)
    {
        $target = User::where('role', 'admin')->findOrFail($id);

        // Prevent self-deletion
        if ($target->id === Auth::id()) {
            return back()->with('error', 'You cannot revoke your own admin access.');
        }

        $email = $target->email;
        $target->admin_role = null;
        $target->role = 'buyer';
        $target->saveQuietly();

        $this->logAction('admin_account_revoke', "Revoked admin access for: {$email}");

        return redirect()->route('admin.accounts.index')
            ->with('success', "Admin access revoked for '{$target->name}'.");
    }
}
