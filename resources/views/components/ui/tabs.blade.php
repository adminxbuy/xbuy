@props([
    'tabs' => [],
    'active' => '',
])

{{--
    Tabs. Alpine controls which panel is visible.

    Panels are provided as named slots matching each tab id, e.g.:
        <x-ui.tabs :tabs="[['id' => 'open', 'label' => 'Open'], ['id' => 'closed', 'label' => 'Closed']]" active="open">
            <x-slot:open>...open panel...</x-slot:open>
            <x-slot:closed>...closed panel...</x-slot:closed>
        </x-ui.tabs>

    If no matching named slot exists for a tab, the default slot is shown for the
    active tab (useful for single-panel cases).
--}}

@php
    $activeTab = $active ?: (isset($tabs[0]['id']) ? $tabs[0]['id'] : '');
@endphp

<div x-data="{ activeTab: '{{ $activeTab }}' }" {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="inline-flex items-center gap-1 p-1 bg-muted rounded-lg" role="tablist">
        @foreach($tabs as $tab)
            <button
                type="button"
                role="tab"
                @click="activeTab = '{{ $tab['id'] }}'"
                :class="activeTab === '{{ $tab['id'] }}' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md transition-all"
            >
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-background/60 text-muted-foreground tabular-nums">{{ $tab['count'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    @foreach($tabs as $tab)
        @php $panelVar = $tab['id']; @endphp
        <div x-show="activeTab === '{{ $tab['id'] }}'" role="tabpanel" x-cloak>
            {{-- Render the named slot for this tab if provided, else the default slot. --}}
            {{ isset($$panelVar) ? $$panelVar : $slot }}
        </div>
    @endforeach
</div>
