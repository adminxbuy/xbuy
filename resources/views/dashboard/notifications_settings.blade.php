@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            @include('dashboard.partials._sidebar', ['active' => 'notifications'])

            <!-- Right Content Area -->
            <div class="flex-1 space-y-8">
                <!-- Session messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="/dashboard/settings/notifications" method="POST" class="space-y-6" x-data="{
                    notifyUpdates: {{ $user->notify_updates ? 'true' : 'false' }},
                    notifyMarketing: {{ $user->notify_marketing ? 'true' : 'false' }},
                    notifyMessages: {{ $user->notify_messages ? 'true' : 'false' }},
                    notifyFeedback: {{ $user->notify_feedback ? 'true' : 'false' }},
                    notifyDiscounts: {{ $user->notify_discounts ? 'true' : 'false' }},
                    notifyFavorites: {{ $user->notify_favorites ? 'true' : 'false' }},
                    notifyNewItems: {{ $user->notify_new_items ? 'true' : 'false' }},
                    notifyEmail: {{ $user->notify_email ? 'true' : 'false' }}
                }">
                    @csrf
                    
                    <!-- News Section -->
                    <div class="space-y-4">
                        <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">News</span>
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            <!-- Updates -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-zinc-800">Vinted Updates</h4>
                                    <p class="text-xs text-zinc-400">Be the first to know about our newest features, updates, and changes</p>
                                </div>
                                <input type="hidden" name="notify_updates" :value="notifyUpdates ? 1 : 0">
                                <button type="button" @click="notifyUpdates = !notifyUpdates" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyUpdates ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyUpdates ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- Marketing -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-zinc-800">Marketing communications</h4>
                                    <p class="text-xs text-zinc-400">Receive personalized offers, news, and recommendations</p>
                                </div>
                                <input type="hidden" name="notify_marketing" :value="notifyMarketing ? 1 : 0">
                                <button type="button" @click="notifyMarketing = !notifyMarketing" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyMarketing ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyMarketing ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- High-priority Notifications Section -->
                    <div class="space-y-4">
                        <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">High-priority notifications</span>
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            <!-- New messages -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <span class="text-sm font-bold text-zinc-800">New messages</span>
                                <input type="hidden" name="notify_messages" :value="notifyMessages ? 1 : 0">
                                <button type="button" @click="notifyMessages = !notifyMessages" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyMessages ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyMessages ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- New feedback -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <span class="text-sm font-bold text-zinc-800">New feedback</span>
                                <input type="hidden" name="notify_feedback" :value="notifyFeedback ? 1 : 0">
                                <button type="button" @click="notifyFeedback = !notifyFeedback" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyFeedback ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyFeedback ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- Discounted items -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <span class="text-sm font-bold text-zinc-800">Discounted items</span>
                                <input type="hidden" name="notify_discounts" :value="notifyDiscounts ? 1 : 0">
                                <button type="button" @click="notifyDiscounts = !notifyDiscounts" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyDiscounts ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyDiscounts ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Other Notifications Section -->
                    <div class="space-y-4">
                        <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">Other notifications</span>
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            <!-- Favorited items -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <span class="text-sm font-bold text-zinc-800">Favorited Items</span>
                                <input type="hidden" name="notify_favorites" :value="notifyFavorites ? 1 : 0">
                                <button type="button" @click="notifyFavorites = !notifyFavorites" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyFavorites ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyFavorites ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- New items -->
                            <div class="flex items-center justify-between p-5 gap-4">
                                <span class="text-sm font-bold text-zinc-800">New items</span>
                                <input type="hidden" name="notify_new_items" :value="notifyNewItems ? 1 : 0">
                                <button type="button" @click="notifyNewItems = !notifyNewItems" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyNewItems ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                                    <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyNewItems ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- Daily limit -->
                            <div class="p-5 space-y-2">
                                <label for="notify_daily_limit" class="text-sm font-medium text-zinc-500 block">Set a daily limit for each notification type</label>
                                <div class="relative border-b border-zinc-200 pb-2 flex items-center">
                                    <select id="notify_daily_limit" name="notify_daily_limit" class="w-full text-lg text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 pr-8 font-medium cursor-pointer appearance-none">
                                        <option value="1 notification" {{ old('notify_daily_limit', $user->notify_daily_limit) === '1 notification' ? 'selected' : '' }}>1 notification</option>
                                        <option value="Up to 2 notifications" {{ old('notify_daily_limit', $user->notify_daily_limit) === 'Up to 2 notifications' ? 'selected' : '' }}>Up to 2 notifications</option>
                                        <option value="Up to 5 notifications" {{ old('notify_daily_limit', $user->notify_daily_limit) === 'Up to 5 notifications' ? 'selected' : '' }}>Up to 5 notifications</option>
                                        <option value="Up to 10 notifications" {{ old('notify_daily_limit', $user->notify_daily_limit) === 'Up to 10 notifications' ? 'selected' : '' }}>Up to 10 notifications</option>
                                        <option value="Up to 20 notifications" {{ old('notify_daily_limit', $user->notify_daily_limit) === 'Up to 20 notifications' ? 'selected' : '' }}>Up to 20 notifications</option>
                                        <option value="Send me everything" {{ old('notify_daily_limit', $user->notify_daily_limit) === 'Send me everything' ? 'selected' : '' }}>Send me everything</option>
                                    </select>
                                    <div class="absolute right-0 pointer-events-none text-zinc-500">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Notifications -->
                    <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden p-5 flex items-center justify-between gap-4">
                        <span class="text-sm font-bold text-zinc-800 font-bold">Enable email notifications</span>
                        <input type="hidden" name="notify_email" :value="notifyEmail ? 1 : 0">
                        <button type="button" @click="notifyEmail = !notifyEmail" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer" :class="notifyEmail ? 'bg-[#e6c019]' : 'bg-zinc-200'">
                            <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyEmail ? 'translate-x-6' : 'translate-x-0'"></div>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-8 py-3 bg-[#e6c019] text-black font-bold text-sm rounded-sm cursor-pointer">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
