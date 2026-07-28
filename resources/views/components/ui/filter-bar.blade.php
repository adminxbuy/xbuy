@props([
    'action' => '',
    'method' => 'GET',
])

<form action="{{ $action }}" method="{{ $method }}" class="bg-card border border-border rounded-xl p-4 shadow-sm">
    <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
        {{ $slot }}
    </div>
</form>
