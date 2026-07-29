@props(['columns' => []])

{{--
    Table header. Pass a simple array of labels, or use the slot for full control
    (e.g. sortable headers, checkboxes, right-aligned numeric columns).

    Each entry in $columns may be a string, or an array:
        ['label' => 'Amount', 'align' => 'right', 'class' => 'w-32']
--}}

<thead {{ $attributes->merge(['class' => 'border-b border-border']) }}>
    @if(count($columns))
        <tr>
            @foreach($columns as $column)
                @php
                    $label = is_array($column) ? ($column['label'] ?? '') : $column;
                    $align = is_array($column) ? ($column['align'] ?? 'left') : 'left';
                    $extra = is_array($column) ? ($column['class'] ?? '') : '';
                    $alignClass = match ($align) {
                        'right' => 'text-right',
                        'center' => 'text-center',
                        default => 'text-left',
                    };
                @endphp
                <th scope="col" class="px-4 py-3 font-medium text-xs text-muted-foreground whitespace-nowrap {{ $alignClass }} {{ $extra }}">
                    {{ $label }}
                </th>
            @endforeach
        </tr>
    @else
        {{ $slot }}
    @endif
</thead>
