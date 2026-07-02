@extends('layouts.admin')

@section('title', 'Account Settings')
@section('page_title', 'Account Settings')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-500 border border-zinc-200">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-zinc-900 leading-tight">My Profile Settings</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Manage your personal profile details and request updates for credentials.</p>
            </div>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column: Personal Information -->
                <div class="space-y-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 border-b border-zinc-100 pb-2 flex items-center space-x-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>Personal Information</span>
                    </h4>

                    <!-- Name (Request Update) -->
                    <div class="space-y-1.5">
                        <label for="requested_name" class="block text-xs font-semibold text-zinc-700">Full Name</label>
                        <input type="text" id="requested_name" name="requested_name" value="{{ old('requested_name', $user->name) }}" 
                               class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all">
                        <span class="text-[10px] text-amber-600 font-semibold block flex items-center space-x-1">
                            <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                            <span>Changing Name requires Super Admin approval.</span>
                        </span>
                    </div>

                    <!-- Email (Request Update) -->
                    <div class="space-y-1.5">
                        <label for="requested_email" class="block text-xs font-semibold text-zinc-700">Email Address</label>
                        <input type="email" id="requested_email" name="requested_email" value="{{ old('requested_email', $user->email) }}" 
                               class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all">
                        <span class="text-[10px] text-amber-600 font-semibold block flex items-center space-x-1">
                            <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                            <span>Changing Email requires Super Admin approval.</span>
                        </span>
                    </div>

                    <!-- Mobile No (Request Update) -->
                    <div class="space-y-1.5">
                        <label for="requested_mobile" class="block text-xs font-semibold text-zinc-700">Mobile Number</label>
                        <input type="text" id="requested_mobile" name="requested_mobile" value="{{ old('requested_mobile', $user->phone) }}" 
                               class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all">
                        <span class="text-[10px] text-amber-600 font-semibold block flex items-center space-x-1">
                            <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                            <span>Changing Mobile requires Super Admin approval.</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Gender (Direct Update) -->
                        <div class="space-y-1.5">
                            <label for="gender" class="block text-xs font-semibold text-zinc-700">Gender</label>
                            <select id="gender" name="gender" 
                                    class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all cursor-pointer">
                                <option value="" {{ is_null($user->gender) ? 'selected' : '' }}>Select Gender</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <span class="text-[10px] text-zinc-400 block">Updates directly.</span>
                        </div>

                        <!-- DOB (Direct Update) -->
                        <div class="space-y-1.5">
                            <label for="dob" class="block text-xs font-semibold text-zinc-700">Date of Birth</label>
                            <input type="text" id="dob" name="dob" value="{{ old('dob', $user->dob ? \Illuminate\Support\Carbon::parse($user->dob)->format('Y-m-d') : '') }}" 
                                   class="flatpickr-date w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all cursor-pointer">
                            <span class="text-[10px] text-zinc-400 block">Updates directly.</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Financial & Bank Details -->
                <div class="space-y-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 border-b border-zinc-100 pb-2 flex items-center space-x-1.5">
                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        <span>Payment & Bank Details</span>
                    </h4>

                    <!-- Bank Account Details (Encrypted fields in DB, Request Update) -->
                    <div class="space-y-1.5">
                        <label for="requested_bank_account" class="block text-xs font-semibold text-zinc-700">Bank Account Details (Bank Name, Holder Name, Account #, IFSC)</label>
                        <textarea id="requested_bank_account" name="requested_bank_account" rows="4" 
                                  placeholder="e.g. State Bank of India&#10;Holder: Toby Belhome&#10;Account No: 12345678901&#10;IFSC: SBIN0001234"
                                  class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all">{{ old('requested_bank_account', $profile->bank_account) }}</textarea>
                        <span class="text-[10px] text-amber-600 font-semibold block flex items-center space-x-1">
                            <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                            <span>Changing Bank Details requires Super Admin approval.</span>
                        </span>
                    </div>

                    <!-- UPI ID (Request Update) -->
                    <div class="space-y-1.5">
                        <label for="requested_upi_id" class="block text-xs font-semibold text-zinc-700">UPI ID</label>
                        <input type="text" id="requested_upi_id" name="requested_upi_id" value="{{ old('requested_upi_id', $profile->upi_id) }}" 
                               placeholder="e.g. toby@ybl"
                               class="w-full px-3.5 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-400 focus:border-zinc-400 transition-all">
                        <span class="text-[10px] text-amber-600 font-semibold block flex items-center space-x-1">
                            <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                            <span>Changing UPI ID requires Super Admin approval.</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-zinc-100">
                <button type="submit" class="px-6 py-2.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#fbc02d] text-black font-semibold rounded-xl text-sm transition-all shadow-sm flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Profile Changes & Request Updates</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Change Request History -->
    <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm mt-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-500 border border-zinc-200">
                <i data-lucide="history" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-zinc-900 leading-tight">Change Request Records</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Track your submitted credential update requests and their current review status.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-100 text-xs font-bold uppercase text-zinc-400">
                        <th class="py-3 px-4">Date Submitted</th>
                        <th class="py-3 px-4">Requested Updates</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 text-xs">
                    @forelse($changeRequests as $req)
                        <tr>
                            <td class="py-3.5 px-4 font-semibold text-zinc-500 whitespace-nowrap">
                                {{ $req->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 whitespace-pre-line font-medium leading-relaxed">
                                {{ $req->message }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($req->status === 'open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Pending Approval
                                    </span>
                                @elseif($req->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        Under Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Approved / Resolved
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-zinc-400 font-medium">
                                No profile change request records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
