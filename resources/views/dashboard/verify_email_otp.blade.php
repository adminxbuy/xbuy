@extends('layouts.app')

@section('title', 'Verify Code')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'security'])

 <!-- Right Content Area -->
 <div class="flex-1 max-w-md mx-auto md:mx-0 py-6 space-y-6">
 <!-- Status & Success message with OTP -->
 @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm font-semibold">
 {{ session('success') }}
 </div>
 @endif

 @if($errors->any())
 <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-sm text-sm">
 <ul class="list-disc pl-5 space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="space-y-2">
 <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Enter Verification Code</h2>
 <p class="text-sm text-zinc-500">Please enter the 6-digit confirmation code we sent to your email.</p>
 </div>

 <form action="/dashboard/settings/email/verify" method="POST" class="space-y-6">
 @csrf
  <div class="border-b border-zinc-200 focus-within:border-zinc-800 pb-2 transition-colors duration-150">
 <input type="text" name="otp" required maxlength="6" placeholder="e.g. 123456"  class="w-full text-lg text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-300 tracking-widest text-center md:text-left">
 </div>

 <button type="submit" class="w-full md:w-auto px-8 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider block text-center">
 Verify code
 </button>
 </form>
 </div>
 </div>
 </div>
</div>
@endsection
