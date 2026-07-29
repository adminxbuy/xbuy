{{--
    Table wrapper. Mirrors shadcn/ui's Table (see shadcn-ref/src/components/ui/table.tsx):
    a scroll container plus a full-width table.

    Usage:
        <x-ui.table>
            <x-ui.table-header :columns="['Name', 'Email', 'Status']" />
            <tbody>
                <x-ui.table-row>
                    <x-ui.table-cell>...</x-ui.table-cell>
                </x-ui.table-row>
            </tbody>
        </x-ui.table>
--}}

<div class="relative w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm text-left']) }}>
        {{ $slot }}
    </table>
</div>
