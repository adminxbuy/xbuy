@extends('layouts.app')

@section('title', 'Confirm Change')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'security'])

 <!-- Right Content Area -->
 <div class="flex-1 max-w-xl mx-auto md:mx-0 py-6 text-center md:text-left space-y-8">
 <!-- Heading block -->
 <div class="space-y-3">
 <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Confirm change</h2>
 <div class="text-sm text-zinc-650 leading-relaxed space-y-1">
 <p>You need to confirm</p>
 <p class="font-bold text-zinc-900">{{ $user->email }}</p>
 <p>is your email address before you can update it.</p>
 </div>
 </div>

 <!-- Form Action button -->
 <form action="/dashboard/settings/email/send-confirmation" method="POST" class="space-y-4">
 @csrf
 <button type="submit" class="w-full md:w-auto px-8 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider block text-center">
 Send confirmation email
 </button>
 </form>

 <!-- Help link -->
 <div>
 <a href="/dashboard/settings/security" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
 I don't have access to this email
 </a>
 </div>
 </div>
 </div>
 </div>
</div>
@endsection
