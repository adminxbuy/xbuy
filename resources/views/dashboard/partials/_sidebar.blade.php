@php
 $active = $active ?? 'profile';
@endphp

<!-- Left Sidebar Navigation -->
<div class="w-full md:w-1/4 shrink-0 space-y-4">
 <div class="pb-3 border-b border-zinc-200">
 <h2 class="text-xl font-bold text-zinc-900 tracking-tight">Settings</h2>
 </div>
 <nav class="flex flex-col gap-0.5">
 <a href="/dashboard/settings"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'profile' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Profile details
 </a>
 <a href="/dashboard/settings/account"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'account' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Account settings
 </a>
 <a href="/dashboard/settings/shipping"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'shipping' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Shipping
 </a>
 <a href="/dashboard/settings/payments"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'payments' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Payments
 </a>
 <a href="/dashboard/settings/notifications"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'notifications' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Notifications
 </a>
 <a href="/dashboard/settings/privacy"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'privacy' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Privacy settings
 </a>
 <a href="/dashboard/settings/security"  class="px-2 py-2 rounded-sm text-sm {{ $active === 'security' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700' }}">
 Security
 </a>
 </nav>
</div>
