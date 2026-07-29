@extends('layouts.app')

@section('title', 'Subscription Updated')

@section('content')
 <div class="max-w-md mx-auto my-16 px-6">
 <div class="bg-white border border-zinc-200 rounded-3xl p-8 text-center space-y-5">
 <div class="w-14 h-14 bg-zinc-100 text-zinc-800 rounded-full flex items-center justify-center mx-auto">
 <i data-lucide="bell-off" class="w-6 h-6"></i>
 </div>

 <div class="space-y-2">
 <h1 class="text-xl font-bold text-zinc-900 tracking-tight">Subscription Status Updated</h1>
 <p class="text-zinc-500 text-sm leading-relaxed font-medium">
 {{ $message }}
 </p>
 </div>

 <div class="pt-4">
 <a href="/"
 class="inline-flex items-center justify-center bg-[#fdd835] hover:bg-[#ffd747] text-black font-semibold px-6 py-2.5 rounded-xl text-xs shadow-md border border-black/10 transition-all">
 Return to Home
 </a>
 </div>
 </div>
 </div>
@endsection