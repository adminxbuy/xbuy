@extends('layouts.app')

@section('title', 'Welcome to ' . \App\Models\SiteSetting::getVal('platform_name', 'X-Buy'))

@section('content')
    <div class="flex items-center justify-center p-6 lg:p-12 min-h-[calc(100vh-65px)]">
        <div
            class="bg-white border border-zinc-200 rounded-3xl shadow-sm max-w-4xl w-full overflow-hidden flex flex-col md:flex-row">
            <!-- Main Panel -->
            <div class="p-8 md:p-12 flex-1 space-y-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Let's get started</h1>
                    <p class="text-zinc-500 mt-1">India's Safest PC Parts Marketplace with Escrow Protection.</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3.5">
                        <div
                            class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-800 flex-shrink-0">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-sm text-zinc-800">Escrow Security</h4>
                            <p class="text-xs text-zinc-500">Payments are held safely in escrow and only released after your
                                verification window.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3.5">
                        <div
                            class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-800 flex-shrink-0">
                            <i data-lucide="check-square" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-sm text-zinc-800">Verified Component Specs</h4>
                            <p class="text-xs text-zinc-500">Every listing maps exact hardware specifications and
                                verification parameters.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex flex-wrap gap-3">
                    <a href="/listings"
                        class="bg-[#fdd835] hover:bg-[#ffd747] text-black font-semibold py-2.5 px-6 rounded-xl shadow-sm border border-black/10 transition-all text-sm flex items-center space-x-2">
                        <span>Browse Parts</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Visual Side -->
            <div
                class="bg-yellow-50 border-t md:border-t-0 md:border-l border-zinc-200 p-8 md:p-12 w-full md:w-[380px] shrink-0 flex flex-col justify-center items-center text-center space-y-4">
                <div class="w-16 h-16 bg-[#fdd835]/15 text-yellow-800 rounded-2xl flex items-center justify-center">
                    <i data-lucide="tag" class="w-8 h-8"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-800">Have parts to sell?</h3>
                    <p class="text-xs text-zinc-500 mt-1">Turn your old graphics cards, CPUs, or RAM into cash securely.</p>
                </div>
                <a href="/dashboard/listings/create"
                    class="w-full py-2.5 bg-black hover:bg-zinc-950 text-white font-semibold rounded-xl text-xs transition-all shadow-md">
                    Create Listing
                </a>
            </div>
        </div>
    </div>
@endsection