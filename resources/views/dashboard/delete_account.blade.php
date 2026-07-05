@extends('layouts.app')

@section('title', 'Delete Account')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            @include('dashboard.partials._sidebar', ['active' => 'account'])

            <!-- Right Content Area -->
            <div class="flex-1 space-y-8">
                <div class="flex items-center gap-2 text-zinc-500 text-sm">
                    <a href="/dashboard/settings/account" class="hover:text-zinc-800 transition-colors">Account settings</a>
                    <span>/</span>
                    <span class="text-zinc-800 font-medium">Delete my account</span>
                </div>

                <div class="space-y-4">
                    <h2 class="text-2xl font-bold text-zinc-900">Delete my account</h2>
                    
                    @if($errors->any())
                        <div class="p-4 bg-rose-50 text-rose-850 border border-rose-200 rounded-sm text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="/dashboard/settings/delete-account" method="POST" class="space-y-6 bg-white border border-zinc-200 rounded-sm p-6">
                        @csrf
                        
                        <!-- Help us improve -->
                        <div class="flex flex-col md:flex-row md:items-start justify-between py-4 border-b border-zinc-100 gap-4">
                            <div class="md:w-1/3 shrink-0">
                                <h4 class="text-sm font-bold text-zinc-800">Help us improve</h4>
                            </div>
                            <div class="flex-1 max-w-lg">
                                <textarea name="reason" rows="3" placeholder="Tell us why you're closing your account" class="w-full text-sm text-zinc-700 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none placeholder-zinc-400 focus:ring-0 resize-none pb-2"></textarea>
                            </div>
                        </div>

                        <!-- Confirm password -->
                        @if(!auth()->user()->google_linked && !auth()->user()->facebook_linked && !auth()->user()->apple_linked)
                        <div class="flex flex-col md:flex-row md:items-center justify-between py-4 border-b border-zinc-100 gap-4">
                            <div class="md:w-1/3 shrink-0">
                                <h4 class="text-sm font-bold text-zinc-800">Confirm password</h4>
                            </div>
                            <div class="flex-1 max-w-lg">
                                <input type="password" name="delete_password" required placeholder="Enter your current password" class="w-full text-sm text-zinc-700 bg-transparent border-b border-zinc-200 focus:border-rose-500 focus:ring-0 outline-none placeholder-zinc-400 pb-2">
                            </div>
                        </div>
                        @endif

                        <!-- I confirm that all my orders are completed -->
                        <div class="space-y-6 pt-4">
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-sm font-bold text-zinc-800">I confirm that all my orders are completed</span>
                                <div class="shrink-0 flex items-center">
                                    <input type="checkbox" id="confirm_orders" required class="w-5 h-5 text-emerald-600 bg-white border-zinc-300 rounded focus:ring-emerald-500 cursor-pointer">
                                </div>
                            </div>

                            <p class="text-xs text-zinc-400 leading-relaxed">
                                If you delete your account, it will be deactivated immediately. Deactivated accounts are only visible to Team X-Buy before they are permanently deleted. The deletion takes place within the time frames indicated in X-Buy's <a href="#" class="underline hover:text-zinc-500">Privacy Policy</a>.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-6 py-2.5 bg-rose-600 text-white font-bold text-sm rounded-sm hover:bg-rose-700 transition-colors cursor-pointer">
                                Delete account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
