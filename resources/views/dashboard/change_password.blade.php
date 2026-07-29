@extends('layouts.app')

@section('title', 'Change Password')

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
 <span class="text-zinc-800 font-medium">Change password</span>
 </div>

 <div class="space-y-4">
 <h2 class="text-2xl font-bold text-zinc-900">Change password</h2>

 @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
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

 <!-- Two-column card container -->
 <div class="bg-white border border-zinc-200 rounded-sm p-6 md:p-10">
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 md:gap-16">
  <!-- Left Column: Guidelines -->
 <div class="space-y-4">
 <h4 class="font-bold text-zinc-800 text-[15px]">To create a secure password:</h4>
 <ul class="space-y-4 text-sm text-zinc-500 list-disc pl-5 leading-relaxed">
 <li>
 When setting up a password, go for something that is not too obvious. It can be a combination of numbers, special characters, uppercase and lowercase letters. The length of the password should be at least 8 characters.
 </li>
 <li>
 Don't use your name or date of birth when setting up a password.
 </li>
 <li>
 Memorize your password. Do not keep any record of it, do not tell other people about it. Try to change it regularly.
 </li>
 <li>
 Make sure no one can see you entering the password.
 </li>
 </ul>
 </div>

 <!-- Right Column: Password Form -->
 <form action="/dashboard/settings/change-password" method="POST" class="space-y-8 flex flex-col justify-center">
 @csrf

 <!-- Current Password -->
 <div>
 <input type="password" id="current_password" name="current_password" required  placeholder="Current password"  class="w-full text-base text-zinc-850 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none pb-2.5 font-medium placeholder-zinc-400">
 </div>

 <!-- New Password -->
 <div>
 <input type="password" id="new_password" name="new_password" required  placeholder="New password"  class="w-full text-base text-zinc-850 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none pb-2.5 font-medium placeholder-zinc-400">
 </div>

 <!-- Reenter New Password -->
 <div>
 <input type="password" id="new_password_confirmation" name="new_password_confirmation" required  placeholder="Reenter your new password"  class="w-full text-base text-zinc-850 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none pb-2.5 font-medium placeholder-zinc-400">
 </div>

 <!-- Submit Button -->
 <div class="pt-4">
 <button type="submit" class="w-full py-3 bg-[#e6c019] hover:bg-[#fdd835] text-black font-bold text-sm rounded-sm transition-colors cursor-pointer text-center">
 Change password
 </button>
 </div>
 </form>

 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
</div>
@endsection
