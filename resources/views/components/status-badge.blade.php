@props(['status', 'color'])

<span class="badge badge-{{ $color }}">{{ str_replace('_', ' ', $status) }}</span>
