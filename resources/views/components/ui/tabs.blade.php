@props([
    'tabs' => [],
    'active' => '',
])

<div x-data="{ activeTab: '{{ $active }}' }" class="space-y-4">
    <div class="flex items-center gap-1 p-1 bg-muted rounded-lg w-fit">
        @foreach($tabs as $tab)
            <button
                @click="activeTab = '{{ $tab['id'] }}'"
                :class="activeTab === '{{ $tab['id'] }}' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                class="px-4 py-2 text-xs font-semibold rounded-md transition-all"
            >
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full bg-muted text-muted-foreground">{{ $tab['count'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    @foreach($tabs as $tab)
        <div x-show="activeTab === '{{ $tab['id'] }}'" x-cloak>
            {{ $$tab['id'] ?? $slot }}
        </div>
    @endforeach
</div>
