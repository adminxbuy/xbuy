@extends('layouts.admin')

@section('title', 'Mail Management')
@section('page_title', 'Mail Management')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'subscribers') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
        <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
        <span>Trash ({{ $trashedSubscribers->count() }})</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    tab: '{{ request('tab', 'subscribers') }}',
    selectedIds: [], 
    selectAll: false, 
    bulkAction: '',
    htmlContent: '',
    timeline: '{{ request('timeline', '') }}',
    activeSubTemplate: 'layout',
    
    // Add Subscriber Modal State
    showAddModal: false,
    addModalTab: 'manual',
    userSearchQuery: '',
    searchedUsers: [],
    selectedImportEmails: [],
    importSuccessMsg: '',
    importErrorMsg: '',
    
    async fetchUsers() {
        try {
            let response = await fetch('{{ route('admin.mails.users.search') }}?search=' + encodeURIComponent(this.userSearchQuery));
            this.searchedUsers = await response.json();
        } catch(e) {
            console.error(e);
        }
    },
    
    async submitImport() {
        if(this.selectedImportEmails.length === 0) return;
        this.importSuccessMsg = '';
        this.importErrorMsg = '';
        try {
            let response = await fetch('{{ route('admin.mails.users.import') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ emails: this.selectedImportEmails })
            });
            let res = await response.json();
            if(res.success) {
                this.importSuccessMsg = res.message;
                setTimeout(() => {
                    this.showAddModal = false;
                    window.location.reload();
                }, 1200);
            } else {
                this.importErrorMsg = res.message || 'Failed to import users.';
            }
        } catch(e) {
            this.importErrorMsg = 'Failed to import users.';
        }
    },

    insertPlaceholder(placeholder) {
        let textarea = $refs.bodyArea;
        let start = textarea.selectionStart;
        let end = textarea.selectionEnd;
        let text = textarea.value;
        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    }
}" x-init="
    $watch('showAddModal', value => {
        if(value) {
            fetchUsers();
        }
    });
    $watch('userSearchQuery', () => {
        fetchUsers();
    });
">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Mails & Sales Alerts</h1>
            <p class="text-muted-foreground text-sm">Send dynamic updates, sales alerts, and email notifications to subscribers and portal users.</p>
        </div>
    </div>

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 text-xs font-semibold">
            {{ session('info') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 rounded-xl p-4 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="border-b border-border flex space-x-6">
        <button @click="tab = 'subscribers'" :class="tab === 'subscribers' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition-all">
            Subscribers List ({{ $subscribers->total() }})
        </button>
        <button @click="tab = 'compose'" :class="tab === 'compose' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition-all">
            Compose & Send Alert
        </button>
        <button @click="tab = 'history'" :class="tab === 'history' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition-all">
            Sent History & Campaigns
        </button>
        <button @click="tab = 'templates'" :class="tab === 'templates' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition-all">
            System Mail Templates
        </button>
    </div>

    <!-- Tab 1: Subscribers List -->
    <div x-show="tab === 'subscribers'" class="space-y-6" x-transition>
        <div class="space-y-4">
            <!-- Filters & Buttons Toolbar -->
            <div class="bg-card border border-border rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
                <form action="{{ route('admin.mails.index') }}" method="GET" class="w-full flex flex-col md:flex-row gap-2">
                    <input type="hidden" name="tab" value="subscribers">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email or name..." 
                               class="w-full pl-9 pr-4 py-2 border border-border rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground w-3.5 h-3.5"></i>
                    </div>
                    <select name="status" class="py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                    <select name="source" class="py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        <option value="">All Sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $src)) }}</option>
                        @endforeach
                    </select>
                    
                    <select name="timeline" x-model="timeline" class="py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        <option value="">All Time</option>
                        <option value="7_days" {{ request('timeline') === '7_days' ? 'selected' : '' }}>Last 7 Days (Week)</option>
                        <option value="15_days" {{ request('timeline') === '15_days' ? 'selected' : '' }}>Last 15 Days</option>
                        <option value="30_days" {{ request('timeline') === '30_days' ? 'selected' : '' }}>Last 30 Days (Month)</option>
                        <option value="90_days" {{ request('timeline') === '90_days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="custom" {{ request('timeline') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>

                    <div x-show="timeline === 'custom'" class="flex items-center space-x-1" x-transition style="display: none;">
                        <input type="text" name="start_date" value="{{ request('start_date') }}" class="flatpickr-date py-2 px-2 border border-border rounded-xl text-xs focus:outline-none" placeholder="Start Date">
                        <span class="text-[10px] text-muted-foreground font-bold">to</span>
                        <input type="text" name="end_date" value="{{ request('end_date') }}" class="flatpickr-date py-2 px-2 border border-border rounded-xl text-xs focus:outline-none" placeholder="End Date">
                    </div>

                    <button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90 font-bold py-2 px-4 rounded-xl text-xs shrink-0">Filter</button>
                    @if(request()->anyFilled(['search', 'status', 'source', 'timeline']))
                        <a href="{{ route('admin.mails.index', ['tab' => 'subscribers']) }}" class="bg-muted hover:bg-muted text-foreground font-bold py-2 px-4 rounded-xl text-xs shrink-0 flex items-center justify-center">Reset</a>
                    @endif
                </form>

                <button type="button" @click="showAddModal = true" class="bg-primary text-primary-foreground hover:bg-primary/90 font-bold py-2 px-5 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 transition-all shadow-md active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Add Subscriber</span>
                </button>
            </div>

            <!-- Bulk Actions Sub-bar -->
            <div x-show="selectedIds.length > 0" class="bg-muted border border-border rounded-xl p-4 flex items-center justify-between gap-4">
                <span class="text-xs text-yellow-900 font-semibold">Selected <span x-text="selectedIds.length"></span> item(s).</span>
                <form action="{{ route('admin.mails.subscribers.bulk-action') }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <template x-for="id in selectedIds">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <select name="action" x-model="bulkAction" required class="py-1.5 px-3 border border-yellow-300 rounded-xl text-xs bg-card">
                        <option value="">Bulk action...</option>
                        <option value="activate">Activate</option>
                        <option value="unsubscribe">Unsubscribe</option>
                        <option value="delete">Delete Permanently</option>
                    </select>
                    <button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground font-bold px-3 py-1.5 rounded-xl text-xs">Apply</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted border-b border-border">
                                <th class="py-3.5 px-4 w-10 text-center">
                                    <input type="checkbox" x-model="selectAll" 
                                           @change="if(selectAll) { selectedIds = @json($subscribers->pluck('id')->toArray()) } else { selectedIds = [] }"
                                           class="rounded border-border text-yellow-500 w-3.5 h-3.5">
                                </th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Subscriber Email</th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Name</th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Source</th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide font-medium">Date Joined</th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Status</th>
                                <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($subscribers as $sub)
                                <tr class="hover:bg-muted/50">
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="checkbox" :value="{{ $sub->id }}" x-model="selectedIds"
                                               @change="selectAll = (selectedIds.length === {{ $subscribers->count() }})"
                                               class="rounded border-border text-yellow-500 w-3.5 h-3.5">
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-sm text-foreground">{{ $sub->email }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs text-muted-foreground">{{ $sub->name ?: '-' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if(in_array($sub->source, ['manual', 'admin_user_import']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                                Admin Added
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-705 border border-emerald-200/60">
                                                Self Subscribed
                                            </span>
                                        @endif
                                        <div class="text-[9px] text-muted-foreground mt-0.5">Via {{ str_replace('_', ' ', $sub->source) }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-xs font-semibold text-foreground">{{ $sub->created_at->format('M d, Y h:i A') }}</div>
                                        <div class="text-[9px] text-muted-foreground mt-0.5">{{ $sub->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($sub->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Unsubscribed</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <form action="{{ route('admin.mails.subscribers.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Remove this subscriber?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-muted-foreground hover:text-red-650 p-1 hover:bg-red-50 rounded-lg">
                                                <i data-lucide="trash" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-muted-foreground">
                                        <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 text-muted-foreground"></i>
                                        <p class="text-xs font-semibold">No subscribers yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($subscribers->hasPages())
                    <div class="px-4 py-3 border-t border-border">
                        {{ $subscribers->appends(['tab' => 'subscribers'])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tab 2: Compose & Send -->
    <div x-show="tab === 'compose'" x-transition>
        <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-foreground mb-4">Compose Dynamic Alert / Broadcast Campaign</h2>
            <form action="{{ route('admin.mails.send') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf
                <!-- Left 2 Cols: Content -->
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Email Subject line *</label>
                        <input type="text" name="subject" required placeholder="e.g. 🔥 Price Drop: RTX 4070 Graphics Cards on Sale!"
                               class="w-full px-4 py-2.5 border border-border rounded-xl focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring font-semibold text-sm">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Email HTML Content *</label>
                            <!-- Shortcuts -->
                            <div class="flex space-x-1.5">
                                <button type="button" @click="insertPlaceholder('<p>Hello Friend,</p>')" class="text-[10px] bg-muted hover:bg-muted text-foreground px-2 py-0.5 rounded font-bold">Paragraph</button>
                                <button type="button" @click="insertPlaceholder('<a href=\'https://x-buy.in\' style=\'background-color:#09090b;padding:10px 20px;color:#000;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;\'>View Deals</a>')" class="text-[10px] bg-muted hover:bg-muted text-foreground px-2 py-0.5 rounded font-bold">+ Button</button>
                                <button type="button" @click="insertPlaceholder('<strong>🔥 Hot Sale!</strong>')" class="text-[10px] bg-muted hover:bg-muted text-foreground px-2 py-0.5 rounded font-bold">Bold</button>
                            </div>
                        </div>
                        <textarea name="body" x-ref="bodyArea" required placeholder="Enter HTML body content here..." rows="12"
                                  class="w-full px-4 py-3 border border-border rounded-xl focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring text-sm font-mono"></textarea>
                    </div>
                </div>

                <!-- Right 1 Col: Target parameters -->
                <div class="space-y-4">
                    <div class="bg-muted border border-border rounded-xl p-5 space-y-4">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wider pb-1.5 border-b border-border">Campaign Target Audience</h3>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Target Group *</label>
                            <select name="target_group" required class="w-full py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none">
                                <option value="all_subscribers">All Active Subscribers (Footer/Clicks)</option>
                                <option value="all_users">All Registered Users</option>
                                <option value="sellers">Sellers Only</option>
                                <option value="buyers">Buyers Only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Alert Type *</label>
                            <select name="type" required class="w-full py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none">
                                <option value="sales_alert">Sales Alert (Deals & Price Cuts)</option>
                                <option value="newsletter">Newsletter (Weekly digest)</option>
                                <option value="general_alert">General Updates / Mail alerts</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Schedule Broadcast (Optional)</label>
                            <input type="text" name="scheduled_at" class="flatpickr-datetime w-full py-2 px-3 border border-border rounded-xl text-xs bg-card focus:outline-none" placeholder="Select date & time">
                            <p class="text-[9px] text-muted-foreground mt-1">Leave empty to send instantly.</p>
                        </div>

                        <div class="bg-muted border border-border rounded-xl p-3.5 space-y-1.5">
                            <h4 class="text-xs font-bold text-yellow-900 flex items-center gap-1.5">
                                <i data-lucide="shield-alert" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>SMTP Verification</span>
                            </h4>
                            <p class="text-[10px] text-foreground leading-relaxed font-medium">Mails are dispatched through your configured platform SMTP server. Verify keys in <a href="{{ route('admin.settings') }}" class="underline font-bold">SMTP Settings</a>.</p>
                        </div>

                        <button type="submit" onclick="return confirm('Confirm campaign submission? If scheduled, it will queue for auto-delivery.');" 
                                class="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-bold py-2.5 rounded-xl text-sm transition-all shadow-md active:scale-95 flex items-center justify-center space-x-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Submit Broadcast Campaign</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: Sent Campaign History -->
    <div x-show="tab === 'history'" x-transition>
        <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-muted border-b border-border">
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Campaign Subject</th>
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Target Group</th>
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Alert Type</th>
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Recipients (Sent)</th>
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Status</th>
                            <th class="py-3.5 px-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Sent Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($campaigns as $camp)
                            <tr class="hover:bg-muted/50">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-sm text-foreground">{{ $camp->subject }}</div>
                                    <div class="text-[10px] text-muted-foreground mt-0.5 line-clamp-1">{{ strip_tags($camp->body) }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-muted-foreground font-medium">
                                    {{ ucwords(str_replace('_', ' ', $camp->target_group)) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted text-foreground">
                                        {{ ucwords(str_replace('_', ' ', $camp->type)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-sm font-semibold text-foreground">
                                    {{ number_format($camp->recipient_count) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($camp->status === 'sent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-705 border border-emerald-200/60">Sent</span>
                                    @elseif($camp->status === 'scheduled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-705 border border-blue-200/60 animate-pulse">Scheduled</span>
                                    @elseif($camp->status === 'sending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Sending...</span>
                                    @elseif($camp->status === 'partial')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-705 border border-amber-200/60">Partial</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Failed</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-muted-foreground font-medium">
                                    @if($camp->status === 'scheduled' && $camp->scheduled_at)
                                        <div class="text-blue-600 font-bold">Queue:</div>
                                        <div>{{ $camp->scheduled_at->format('M d, Y h:i A') }}</div>
                                    @else
                                        {{ $camp->sent_at ? $camp->sent_at->format('M d, Y h:i A') : $camp->created_at->format('M d, Y h:i A') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-muted-foreground">
                                    <i data-lucide="mail" class="w-8 h-8 mx-auto mb-2 text-muted-foreground"></i>
                                    <p class="text-xs font-semibold">No sent campaigns log found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($campaigns->hasPages())
                <div class="px-4 py-3 border-t border-border">
                    {{ $campaigns->appends(['tab' => 'history'])->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Tab 4: System Mail Templates -->
    <div x-show="tab === 'templates'" x-transition class="space-y-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="settings_group" value="mail_templates">
            
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar navigation of the templates -->
                <div class="w-full lg:w-1/4 shrink-0 bg-card border border-border rounded-xl p-4 shadow-sm h-fit space-y-1">
                    <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-3 mb-2">Select Template</h3>
                    
                    <button type="button" @click="activeSubTemplate = 'layout'"
                            :class="activeSubTemplate === 'layout' ? 'bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground font-semibold shadow-sm' : 'text-muted-foreground hover:bg-muted'"
                            class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                        <i data-lucide="layout" class="w-4 h-4"></i>
                        <span>Shared Header / Footer</span>
                    </button>
                    
                    @foreach([
                        'payment_success' => '1. Buyer Payment Success',
                        'escrow_hold' => '2. Seller Escrow Hold Alert',
                        'escrow_released' => '3. Seller Payout Success',
                        'payout_failed' => '4. Payout Failure Warning',
                        'dispute_raised' => '5. Dispute Opened Warning',
                        'dispute_resolved' => '6. Dispute Resolved Outcome',
                        'auth_otp' => '7. OTP Login Verification',
                        'badge_updated' => '8. Seller Badge Recalculated',
                        'dispute_reopened' => '9. Dispute Reopened',
                        'support_ticket' => '10. Support Ticket Raised'
                    ] as $key => $title)
                        <button type="button" @click="activeSubTemplate = '{{ $key }}'"
                                :class="activeSubTemplate === '{{ $key }}' ? 'bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground font-semibold shadow-sm' : 'text-muted-foreground hover:bg-muted'"
                                class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span>{{ $title }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Editor for the selected template -->
                <div class="flex-1 bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                    
                    <!-- 0. Shared Layout Header/Footer Template -->
                    <div x-show="activeSubTemplate === 'layout'" class="space-y-6" x-transition>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="bg-muted text-foreground text-[10px] font-bold px-2 py-0.5 rounded-full">Global Layout</span>
                                <h3 class="text-base font-bold text-foreground">Shared Header & Footer Wrapper</h3>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">This HTML layout wraps all system emails. Modify the header and footer style to match your brand's aesthetics.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach(['mail_template_header', 'mail_template_footer'] as $key)
                                @if($setting = $settings->firstWhere('key', $key))
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">{{ str_replace('mail_template_', 'Layout ', $setting->key) }}</label>
                                        <textarea name="{{ $setting->key }}" required rows="14" class="w-full p-3 border border-border rounded-xl text-xs font-mono focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card">{{ $setting->value }}</textarea>
                                        <p class="text-[10px] text-muted-foreground leading-relaxed">{{ $setting->description }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- 1. Buyer Payment Success -->
                    @php
                        $triggers = [
                            'payment_success' => [
                                'title' => 'Buyer Payment Success Alert',
                                'recipient' => 'Buyer',
                                'trigger' => 'Triggered automatically when a buyer completes payment via Razorpay. The order is then locked into escrow.',
                                'vars' => ['buyer_name', 'order_number', 'order_amount', 'product_title', 'seller_shop_name']
                            ],
                            'escrow_hold' => [
                                'title' => 'Seller Escrow Hold Alert',
                                'recipient' => 'Seller',
                                'trigger' => 'Triggered automatically alongside the payment success alert to notify the seller that funds are held in escrow, prompting them to prepare and dispatch shipment.',
                                'vars' => ['seller_name', 'buyer_name', 'order_number', 'product_title', 'seller_payout_amount', 'testing_days']
                            ],
                            'escrow_released' => [
                                'title' => 'Seller Payout Success Alert',
                                'recipient' => 'Seller',
                                'trigger' => 'Triggered automatically when the testing window expires without dispute, or manually released by administrator/buyer. Confirms that funds have been transferred to their linked bank details.',
                                'vars' => ['seller_name', 'order_number', 'seller_payout_amount']
                            ],
                            'payout_failed' => [
                                'title' => 'Payout Failure Warning',
                                'recipient' => 'Seller & Administrator',
                                'trigger' => 'Triggered when the payout to the seller fails (due to invalid razorpay_account_id, incomplete bank validation, or other api errors). Prompts the seller to verify details.',
                                'vars' => ['seller_name', 'order_number', 'seller_payout_amount', 'payout_error_message']
                            ],
                            'dispute_raised' => [
                                'title' => 'Dispute Opened Warning',
                                'recipient' => 'Seller & Administrator',
                                'trigger' => 'Triggered when the buyer files a dispute during the testing window, suspending payouts and placing the escrow on hold.',
                                'vars' => ['buyer_name', 'order_number', 'product_title', 'dispute_reason', 'dispute_description']
                            ],
                            'dispute_resolved' => [
                                'title' => 'Dispute Resolved Outcome Alert',
                                'recipient' => 'Buyer & Seller',
                                'trigger' => 'Triggered upon final resolution of a dispute by the administrator, confirming split ratios, refund values, and seller payouts.',
                                'vars' => ['order_number', 'resolution_notes', 'buyer_payout', 'seller_payout']
                            ],
                            'auth_otp' => [
                                'title' => 'OTP Login Verification',
                                'recipient' => 'User (Requesting Login)',
                                'trigger' => 'Triggered when a user requests a verification code to log in, verify transactions, or update account security details.',
                                'vars' => ['otp']
                            ],
                            'badge_updated' => [
                                'title' => 'Seller Badge Recalculated Alert',
                                'recipient' => 'Seller',
                                'trigger' => 'Triggered automatically when the cron scheduler recalculates seller performance ratings, reviews, and transaction counts, upgrading or downgrading their status badge.',
                                'vars' => ['previous_badge', 'new_badge']
                            ],
                            'dispute_reopened' => [
                                'title' => 'Dispute Reopened Alert',
                                'recipient' => 'Buyer & Seller',
                                'trigger' => 'Triggered when the administrator reopens a previously resolved dispute for secondary investigation.',
                                'vars' => ['order_number']
                            ],
                            'support_ticket' => [
                                'title' => 'New Support Ticket Raised',
                                'recipient' => 'Administrator',
                                'trigger' => 'Triggered when a buyer or seller creates a new support ticket in the help desk, notifying admins to review it.',
                                'vars' => ['user_name', 'user_email', 'ticket_subject', 'ticket_message']
                            ]
                        ];
                    @endphp

                    @foreach($triggers as $key => $meta)
                        <div x-show="activeSubTemplate === '{{ $key }}'" class="space-y-6" x-transition>
                            <!-- Template Info & Header -->
                            <div class="border-b border-border pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="bg-primary text-primary-foreground text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                        <i data-lucide="send" class="w-3 h-3"></i> Recipient: {{ $meta['recipient'] }}
                                    </span>
                                    <h3 class="text-base font-bold text-foreground">{{ $meta['title'] }}</h3>
                                </div>
                                
                                <!-- Trigger description -->
                                <div class="mt-3 p-3 bg-muted border border-border rounded-xl flex items-start gap-2.5">
                                    <i data-lucide="info" class="w-4.5 h-4.5 text-muted-foreground shrink-0 mt-0.5"></i>
                                    <div>
                                        <h4 class="text-xs font-bold text-foreground">Trigger Event:</h4>
                                        <p class="text-xs text-muted-foreground leading-relaxed mt-0.5">{{ $meta['trigger'] }}</p>
                                    </div>
                                </div>

                                <!-- Dynamic tokens list -->
                                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                                    <span class="font-bold">Dynamic Tokens:</span>
                                    @foreach($meta['vars'] as $v)
                                        <code class="bg-muted border border-border text-yellow-900 px-1.5 py-0.5 rounded font-mono text-[10px] font-semibold">{{"{".$v."}"}}</code>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Inputs -->
                            <div class="grid grid-cols-1 gap-4">
                                @php
                                    $subjSetting = $settings->firstWhere('key', "mail_template_{$key}_subject");
                                    $bodySetting = $settings->firstWhere('key', "mail_template_{$key}_body");
                                @endphp
                                @if($subjSetting)
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Email Subject Line</label>
                                        <input type="text" name="{{ $subjSetting->key }}" value="{{ $subjSetting->value }}" required class="w-full p-3 border border-border rounded-xl text-sm focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card font-semibold">
                                    </div>
                                @endif
                                @if($bodySetting)
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Email HTML Body Content</label>
                                        <textarea name="{{ $bodySetting->key }}" required rows="10" class="w-full p-3 border border-border rounded-xl text-sm focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none bg-card font-mono leading-relaxed">{{ $bodySetting->value }}</textarea>
                                        <p class="text-[10px] text-muted-foreground leading-relaxed">{{ $bodySetting->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Save footer bar -->
                    <div class="pt-5 border-t border-border flex justify-end">
                        <button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90 font-semibold py-2.5 px-6 rounded-lg ring-0 transition-all text-xs active:scale-[0.98] flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Save Email Templates</span>
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Subscriber Addition Modal Overlay -->
    <div x-show="showAddModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showAddModal = false"
         style="display: none;">
         
        <div class="bg-card border border-border rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="showAddModal = false">
             
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-muted border-b border-border flex items-center justify-between">
                <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-foreground"></i>
                    <span>Add New Subscribers</span>
                </h3>
                <button type="button" @click="showAddModal = false" class="text-muted-foreground hover:text-foreground p-1 rounded-lg hover:bg-muted transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Modal Tabs -->
            <div class="border-b border-border px-6 flex space-x-4 bg-card">
                <button @click="addModalTab = 'manual'" :class="addModalTab === 'manual' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        class="py-2.5 px-1 border-b-2 text-xs font-semibold uppercase tracking-wider transition-all">
                    Manual Form
                </button>
                <button @click="addModalTab = 'import'" :class="addModalTab === 'import' ? 'border-foreground text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        class="py-2.5 px-1 border-b-2 text-xs font-semibold uppercase tracking-wider transition-all">
                    Search & Import Users
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 max-h-[380px] overflow-y-auto">
                <!-- Tab: Manual -->
                <div x-show="addModalTab === 'manual'" class="space-y-4">
                    <form action="{{ route('admin.mails.subscribers.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Email Address *</label>
                            <input type="email" name="email" required placeholder="name@example.com"
                                   class="w-full px-4 py-2 border border-border rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Subscriber Name</label>
                            <input type="text" name="name" placeholder="John Doe"
                                   class="w-full px-4 py-2 border border-border rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Collection Source</label>
                            <input type="text" name="source" placeholder="manual" value="manual"
                                   class="w-full px-4 py-2 border border-border rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        </div>
                        
                        <div class="pt-4 border-t border-border flex justify-end space-x-2">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-muted hover:bg-muted text-foreground font-bold rounded-xl text-xs">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 bg-primary text-primary-foreground hover:bg-primary/90 font-bold rounded-xl text-xs shadow-md">
                                Add Subscriber
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab: Import -->
                <div x-show="addModalTab === 'import'" class="space-y-4">
                    <!-- Feedback alerts -->
                    <div x-show="importSuccessMsg" class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl p-3" x-text="importSuccessMsg"></div>
                    <div x-show="importErrorMsg" class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl p-3" x-text="importErrorMsg"></div>

                    <!-- Search user box -->
                    <div class="relative">
                        <input type="text" x-model="userSearchQuery" placeholder="Search system users by name or email..." 
                               class="w-full pl-9 pr-4 py-2 border border-border rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground w-3.5 h-3.5"></i>
                    </div>

                    <!-- User listing selection list -->
                    <div class="border border-border rounded-xl overflow-hidden bg-muted max-h-[200px] overflow-y-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-muted border-b border-border text-[10px] font-bold text-muted-foreground">
                                    <th class="py-2.5 px-3 w-8 text-center">
                                        <input type="checkbox" 
                                               @change="if($el.checked) { selectedImportEmails = searchedUsers.map(u => u.email) } else { selectedImportEmails = [] }"
                                               class="rounded border-border text-yellow-500 w-3 h-3">
                                    </th>
                                    <th class="py-2.5 px-3 uppercase tracking-wider">User Details</th>
                                    <th class="py-2.5 px-3 uppercase tracking-wider">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <template x-for="user in searchedUsers" :key="user.id">
                                    <tr class="hover:bg-muted">
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="checkbox" :value="user.email" x-model="selectedImportEmails"
                                                   class="rounded border-border text-yellow-500 w-3 h-3">
                                        </td>
                                        <td class="py-2.5 px-3 font-semibold text-foreground" x-text="user.name"></td>
                                        <td class="py-2.5 px-3 text-muted-foreground" x-text="user.email"></td>
                                    </tr>
                                </template>
                                <template x-if="searchedUsers.length === 0">
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-muted-foreground italic">No searchable users found.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-border flex items-center justify-between">
                        <span class="text-[10px] text-muted-foreground font-bold" x-text="selectedImportEmails.length + ' users selected'"></span>
                        <div class="flex space-x-2">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-muted hover:bg-muted text-foreground font-bold rounded-xl text-xs">
                                Close
                            </button>
                            <button type="button" @click="submitImport" :disabled="selectedImportEmails.length === 0"
                                    class="px-5 py-2 bg-primary text-primary-foreground hover:bg-primary/90 font-bold rounded-xl text-xs shadow-md disabled:opacity-40">
                                Subscribe Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
