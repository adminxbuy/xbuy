@extends('layouts.admin')

@section('title', 'Edit Staff Member')
@section('page_title', 'Edit Staff Access')

@section('content')
@php
    $isPreset = in_array($admin->admin_role, ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator']);
@endphp
<div class="w-full space-y-6" x-data="editStaffPage()">
    {{-- Back Link & Page Title --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.accounts.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 hover:border-zinc-300 px-3 py-2 rounded-xl transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Back to List
        </a>
        <span class="text-xs text-zinc-400 font-medium">Update system permissions or change access passwords</span>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
        <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl px-5 py-4 text-xs shadow-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold mb-1">Please correct the following errors:</p>
                <ul class="list-disc ml-4 space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form Column --}}
    <form action="{{ route('admin.accounts.update', $admin->id) }}" method="POST" class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')
        
        {{-- Staff Profile Summary header --}}
        <div class="flex items-center gap-4 bg-zinc-50/70 border border-zinc-150 p-4 rounded-xl">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background=e4e4e7&color=71717a&size=48" 
                 class="w-12 h-12 rounded-xl shrink-0 border border-zinc-200" alt="{{ $admin->name }}">
            <div>
                <h3 class="text-sm font-bold text-zinc-900">{{ $admin->name }}</h3>
                <p class="text-xs text-zinc-500">{{ $admin->email }}</p>
                <p class="text-[10px] text-zinc-400 mt-1 font-semibold flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3 h-3"></i> Joined {{ $admin->created_at->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- Editable Name and Email fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Name --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-zinc-700 flex items-center gap-1">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="text" name="name" required value="{{ old('name', $admin->name) }}" placeholder="e.g. Rahul Sharma"
                           class="w-full pl-10 pr-4 py-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-yellow-100 focus:outline-none transition-all">
                </div>
            </div>

            {{-- Email --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-zinc-700 flex items-center gap-1">
                    Email Address <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="email" name="email" required value="{{ old('email', $admin->email) }}" placeholder="username@xbuy.in"
                           class="w-full pl-10 pr-4 py-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-yellow-100 focus:outline-none transition-all">
                </div>
            </div>
        </div>

        {{-- Role Selection --}}
        <div class="space-y-3">
            <label class="text-xs font-bold text-zinc-700 flex items-center gap-1">
                System Access Role <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <template x-for="(role, key) in roles" :key="key">
                    <label class="border rounded-xl p-4 flex flex-col justify-between gap-3 cursor-pointer transition-all hover:bg-zinc-50"
                           :class="selectedRole === key ? 'border-yellow-400 bg-zinc-50/30 ring-1 ring-yellow-400' : 'border-zinc-200 bg-white'">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-xl" :class="role.bgClass">
                                <i :data-lucide="role.icon" class="w-4 h-4" :class="role.iconClass"></i>
                            </div>
                            <input type="radio" name="admin_role" :value="key" required x-model="selectedRole" @change="onRoleChange(key)" class="text-yellow-500 focus:ring-yellow-400">
                        </div>
                        <div>
                            <p class="text-xs font-bold text-zinc-900" x-text="role.label"></p>
                            <p class="text-[10px] text-zinc-500 mt-1 leading-relaxed" x-text="role.shortDesc"></p>
                        </div>
                    </label>
                </template>
            </div>

            {{-- Custom Role Title Input --}}
            <div x-show="selectedRole === 'custom'" x-transition class="space-y-1.5 pt-2">
                <label class="text-xs font-bold text-zinc-750 flex items-center gap-1">
                    Custom Role Name / Title <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="shield" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="text" name="custom_role_title" x-model="customRoleTitle" placeholder="e.g. Manager, Senior Auditor" :required="selectedRole === 'custom'" :disabled="selectedRole !== 'custom'"
                           class="w-full pl-10 pr-4 py-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-yellow-100 focus:outline-none transition-all">
                </div>
                <p class="text-[10px] text-zinc-450">Give this custom permission matrix a user-friendly name (e.g. Manager).</p>
            </div>
        </div>

        {{-- Granular Permissions Matrix --}}
        <div class="border-t border-zinc-150 pt-5 space-y-4">
            <div>
                <h4 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                    <span>Granular Permissions Matrix</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-zinc-100 text-zinc-900 border border-zinc-200">
                        Interactive Scope
                    </span>
                </h4>
                <p class="text-xs text-zinc-500 mt-0.5">Toggle granular permissions. Changing any level will automatically switch the role to custom mode.</p>
            </div>

            <div class="border border-zinc-200 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-zinc-50/80 border-b border-zinc-200 font-bold text-zinc-650">
                            <th class="p-3 pl-4">System Section</th>
                            <th class="p-3 text-center">None</th>
                            <th class="p-3 text-center">View Only</th>
                            <th class="p-3 text-center">Editable</th>
                            <th class="p-3 text-center">Deletable</th>
                            <th class="p-3 text-center">All Access</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <template x-for="(secLabel, secKey) in sections" :key="secKey">
                            <tr class="hover:bg-zinc-50/40 transition-colors">
                                <td class="p-3 pl-4 font-bold text-zinc-850" x-text="secLabel"></td>
                                
                                {{-- Radio Buttons for Access Types --}}
                                <template x-for="type in accessTypes">
                                    <td class="p-3 text-center">
                                        <input type="radio" 
                                               :name="'permissions[' + secKey + ']'" 
                                               :value="type"
                                               x-model="customPerms[secKey]"
                                               @change="if(selectedRole !== 'custom') { selectedRole = 'custom'; }"
                                               class="text-yellow-500 focus:ring-yellow-400">
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Dynamic Access Scope Summary Card --}}
        <div class="bg-zinc-50/30 border border-zinc-200 rounded-xl p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-zinc-900 text-white hover:bg-zinc-800/10 text-zinc-800 rounded-lg border border-zinc-100">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Live System Access Scope</h4>
                </div>
                <span class="text-[10px] text-zinc-500 font-semibold" x-text="roles[selectedRole]?.label || 'Custom Scope'"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                {{-- View-Only Modules --}}
                <div class="space-y-2 bg-white p-3 border border-zinc-150 rounded-xl" x-show="getModulesByAccess('view').length > 0">
                    <span class="text-[10px] text-blue-600 font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> 👁️ View Only
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="mod in getModulesByAccess('view')">
                            <span class="text-[9px] font-semibold px-2 py-0.5 bg-blue-55 text-blue-700 rounded border border-blue-100" x-text="sections[mod]"></span>
                        </template>
                    </div>
                </div>

                {{-- Editable Modules --}}
                <div class="space-y-2 bg-white p-3 border border-zinc-150 rounded-xl" x-show="getModulesByAccess('edit').length > 0">
                    <span class="text-[10px] text-amber-600 font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> ✍️ Editable
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="mod in getModulesByAccess('edit')">
                            <span class="text-[9px] font-semibold px-2 py-0.5 bg-amber-50 text-amber-700 rounded border border-amber-100" x-text="sections[mod]"></span>
                        </template>
                    </div>
                </div>

                {{-- Deletable Modules --}}
                <div class="space-y-2 bg-white p-3 border border-zinc-150 rounded-xl" x-show="getModulesByAccess('delete').length > 0">
                    <span class="text-[10px] text-rose-600 font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 🗑️ Deletable
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="mod in getModulesByAccess('delete')">
                            <span class="text-[9px] font-semibold px-2 py-0.5 bg-rose-50 text-rose-700 rounded border border-rose-100" x-text="sections[mod]"></span>
                        </template>
                    </div>
                </div>

                {{-- Full Access Modules --}}
                <div class="space-y-2 bg-white p-3 border border-zinc-150 rounded-xl" x-show="getModulesByAccess('all').length > 0">
                    <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ⚡ Full Access
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="mod in getModulesByAccess('all')">
                            <span class="text-[9px] font-semibold px-2 py-0.5 bg-emerald-55 text-emerald-700 rounded border border-emerald-100" x-text="sections[mod]"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Password Update Block --}}
        <div class="border-t border-zinc-150 pt-5 space-y-4">
            <div>
                <h4 class="text-xs font-bold text-zinc-900">Change Password</h4>
                <p class="text-[11px] text-zinc-400 mt-0.5">Leave blank if you do not want to change the password.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- New Password --}}
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label class="text-xs font-bold text-zinc-700">New Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                        <input :type="show ? 'text' : 'password'" name="password" placeholder="Leave blank to keep current"
                               class="w-full pl-10 pr-10 py-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-yellow-100 focus:outline-none transition-all">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-700">
                            <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label class="text-xs font-bold text-zinc-700">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" placeholder="Repeat new password"
                               class="w-full pl-10 pr-10 py-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-yellow-100 focus:outline-none transition-all">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-700">
                            <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-150">
            <a href="{{ route('admin.accounts.index') }}" 
               class="px-5 py-3 text-xs font-bold text-zinc-600 border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-all">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-3 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#e6c22f] text-black text-xs font-black rounded-xl flex items-center gap-1.5 transition-all shadow-sm">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function editStaffPage() {
    return {
        selectedRole: '{{ $isPreset ? $admin->admin_role : 'custom' }}',
        customRoleTitle: '{{ !$isPreset ? $admin->admin_role : '' }}',
        accessTypes: ['none', 'view', 'edit', 'delete', 'all'],
        sections: {
            sellers: 'Sellers Management',
            listings: 'Listings & Catalog',
            categories: 'Categories Tree',
            spec_templates: 'Spec Templates',
            pages: 'Policy Pages',
            orders: 'Orders Ledger',
            disputes: 'Dispute Cases',
            escrow: 'Escrow Holdings',
            payouts: 'Payout List',
            tickets: 'Support Tickets',
            analytics: 'Financial Analytics',
            users: 'Buyers Management',
            fraud_flags: 'Fraud Alerts',
            ratings: 'Seller Ratings',
            alerts: 'Platform Alerts',
            mails: 'Newsletter & Mail'
        },
        roles: {
            super_admin: {
                label: 'Super Admin',
                icon: 'crown',
                bgClass: 'bg-violet-50 text-violet-750',
                iconClass: 'text-violet-750',
                shortDesc: 'Absolute permissions over all sections & settings.',
                presets: {
                    sellers: 'all', listings: 'all', categories: 'all', spec_templates: 'all', pages: 'all',
                    orders: 'all', disputes: 'all', escrow: 'all', payouts: 'all', tickets: 'all',
                    analytics: 'all', users: 'all', fraud_flags: 'all', ratings: 'all', alerts: 'all', mails: 'all'
                }
            },
            operations: {
                label: 'Operations Manager',
                icon: 'settings-2',
                bgClass: 'bg-blue-50 text-blue-600',
                iconClass: 'text-blue-600',
                shortDesc: 'Handles daily site inventory and user support checks.',
                presets: {
                    sellers: 'all', listings: 'all', categories: 'edit', spec_templates: 'all', pages: 'none',
                    orders: 'edit', disputes: 'all', escrow: 'none', payouts: 'none', tickets: 'none',
                    analytics: 'none', users: 'all', fraud_flags: 'none', ratings: 'none', alerts: 'none', mails: 'none'
                }
            },
            support: {
                label: 'Support Representative',
                icon: 'headphones',
                bgClass: 'bg-teal-50 text-teal-600',
                iconClass: 'text-teal-600',
                shortDesc: 'Resolves disputes and customer queries directly.',
                presets: {
                    sellers: 'none', listings: 'none', categories: 'none', spec_templates: 'none', pages: 'none',
                    orders: 'view', disputes: 'none', escrow: 'none', payouts: 'none', tickets: 'all',
                    analytics: 'none', users: 'none', fraud_flags: 'none', ratings: 'view', alerts: 'none', mails: 'none'
                }
            },
            finance: {
                label: 'Finance Specialist',
                icon: 'banknote',
                bgClass: 'bg-emerald-50 text-emerald-600',
                iconClass: 'text-emerald-600',
                shortDesc: 'Manages platform escrow holds and payout lists.',
                presets: {
                    sellers: 'none', listings: 'none', categories: 'none', spec_templates: 'none', pages: 'none',
                    orders: 'none', disputes: 'none', escrow: 'all', payouts: 'all', tickets: 'none',
                    analytics: 'view', users: 'none', fraud_flags: 'none', ratings: 'none', alerts: 'none', mails: 'none'
                }
            },
            content: {
                label: 'Content Creator',
                icon: 'pen-line',
                bgClass: 'bg-amber-50 text-amber-600',
                iconClass: 'text-amber-600',
                shortDesc: 'Edits public articles, notifications and layout content.',
                presets: {
                    sellers: 'none', listings: 'none', categories: 'all', spec_templates: 'none', pages: 'all',
                    orders: 'none', disputes: 'none', escrow: 'none', payouts: 'none', tickets: 'none',
                    analytics: 'none', users: 'none', fraud_flags: 'none', ratings: 'none', alerts: 'none', mails: 'all'
                }
            },
            moderator: {
                label: 'Moderator / Safety Team',
                icon: 'shield-check',
                bgClass: 'bg-rose-50 text-rose-600',
                iconClass: 'text-rose-600',
                shortDesc: 'Scans fraud flags and suspicious serial listings.',
                presets: {
                    sellers: 'none', listings: 'edit', categories: 'none', spec_templates: 'none', pages: 'none',
                    orders: 'none', disputes: 'none', escrow: 'none', payouts: 'none', tickets: 'none',
                    analytics: 'none', users: 'none', fraud_flags: 'all', ratings: 'all', alerts: 'all', mails: 'none'
                }
            },
            custom: {
                label: 'Custom Scope',
                icon: 'sliders',
                bgClass: 'bg-zinc-100 text-zinc-800',
                iconClass: 'text-zinc-800',
                shortDesc: 'Select custom permissions for individual sections.',
                presets: {
                    sellers: 'none', listings: 'none', categories: 'none', spec_templates: 'none', pages: 'none',
                    orders: 'none', disputes: 'none', escrow: 'none', payouts: 'none', tickets: 'none',
                    analytics: 'none', users: 'none', fraud_flags: 'none', ratings: 'none', alerts: 'none', mails: 'none'
                }
            }
        },
        customPerms: {
            sellers: '{{ $admin->permissions['sellers'] ?? 'none' }}',
            listings: '{{ $admin->permissions['listings'] ?? 'none' }}',
            categories: '{{ $admin->permissions['categories'] ?? 'none' }}',
            spec_templates: '{{ $admin->permissions['spec_templates'] ?? 'none' }}',
            pages: '{{ $admin->permissions['pages'] ?? 'none' }}',
            orders: '{{ $admin->permissions['orders'] ?? 'none' }}',
            disputes: '{{ $admin->permissions['disputes'] ?? 'none' }}',
            escrow: '{{ $admin->permissions['escrow'] ?? 'none' }}',
            payouts: '{{ $admin->permissions['payouts'] ?? 'none' }}',
            tickets: '{{ $admin->permissions['tickets'] ?? 'none' }}',
            analytics: '{{ $admin->permissions['analytics'] ?? 'none' }}',
            users: '{{ $admin->permissions['users'] ?? 'none' }}',
            fraud_flags: '{{ $admin->permissions['fraud_flags'] ?? 'none' }}',
            ratings: '{{ $admin->permissions['ratings'] ?? 'none' }}',
            alerts: '{{ $admin->permissions['alerts'] ?? 'none' }}',
            mails: '{{ $admin->permissions['mails'] ?? 'none' }}'
        },
        
        onRoleChange(roleKey) {
            if (roleKey !== 'custom' || Object.values(this.customPerms).every(v => v === 'none')) {
                const presets = this.roles[roleKey]?.presets || {};
                Object.keys(this.sections).forEach(key => {
                    this.customPerms[key] = presets[key] || 'none';
                });
            }
        },

        getModulesByAccess(level) {
            return Object.keys(this.sections).filter(key => this.customPerms[key] === level);
        },

        init() {
            if (this.selectedRole !== 'custom') {
                this.onRoleChange(this.selectedRole);
            }
            this.$nextTick(() => {
                lucide.createIcons();
            });
            this.$watch('selectedRole', () => {
                this.$nextTick(() => lucide.createIcons());
            });
            this.$watch('customPerms', () => {
                this.$nextTick(() => lucide.createIcons());
            }, { deep: true });
        }
    };
}
</script>
@endpush
