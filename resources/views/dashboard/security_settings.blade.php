@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'security'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 <div class="space-y-1">
 <h3 class="text-lg font-bold text-zinc-900 font-bold">Keep your account secure</h3>
 <p class="text-sm text-zinc-400">
 Review your info to help protect your account.
 </p>
 </div>

 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
 <!-- Email row -->
 <a href="/dashboard/settings/email/confirm" class="p-5 flex items-center justify-between hover:bg-zinc-50 transition-colors group">
 <div class="space-y-0.5">
 <span class="text-sm font-bold text-zinc-800">Email</span>
 <p class="text-xs text-zinc-400">Keep your email up to date.</p>
 </div>
 <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400"></i>
 </a>

 <!-- Password row -->
 <a href="/dashboard/settings/change-password" class="p-5 flex items-center justify-between hover:bg-zinc-50 transition-colors group">
 <div class="space-y-0.5">
 <span class="text-sm font-bold text-zinc-800">Password</span>
 <p class="text-xs text-zinc-400">Protect your account with a stronger password.</p>
 </div>
 <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400"></i>
 </a>

 <!-- 2-step verification row -->
 <a href="/dashboard/settings/security/two-step" class="p-5 flex items-center justify-between hover:bg-zinc-50 transition-colors group block">
 <div class="space-y-0.5">
 <span class="text-sm font-bold text-zinc-800">2-step verification</span>
 <p class="text-xs text-zinc-400">Secure your login with a simple verification step.</p>
 </div>
 <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400"></i>
 </a>

 <!-- Login activity row -->
 <a href="/dashboard/settings/security/sessions" class="p-5 flex items-center justify-between hover:bg-zinc-50 transition-colors group block">
 <div class="space-y-0.5">
 <span class="text-sm font-bold text-zinc-800">Login activity</span>
 <p class="text-xs text-zinc-400">Manage your logged-in devices.</p>
 </div>
 <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400"></i>
 </a>
 </div>
 </div>
 </div>
 </div>
</div>
@endsection
