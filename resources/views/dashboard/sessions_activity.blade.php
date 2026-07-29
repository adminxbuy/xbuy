@extends('layouts.app')

@section('title', 'Review login activity')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'security'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 <!-- Heading details -->
 <div class="space-y-1">
 <h3 class="text-lg font-bold text-zinc-900">Review login activity</h3>
 <p class="text-sm text-zinc-400">
 Each session shows a device logged into your x-buy.in account. If you notice any unusual activity, log out of that session.
 </p>
 </div>

 <!-- Session Card Box -->
 <div class="bg-white border border-zinc-200 rounded-sm p-6">
 <div class="space-y-1">
 <div class="text-[15px] font-bold text-zinc-800">
 {{ $sessionInfo['location'] }}
 </div>
 <div class="text-sm text-[#007f87] font-semibold">
 Now - Current Device
 </div>
 @if(isset($sessionInfo['device']))
 <div class="text-xs text-zinc-450 pt-1.5">
 {{ $sessionInfo['device'] }} &bull; {{ $sessionInfo['ip'] }}
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
 </div>
</div>
@endsection
