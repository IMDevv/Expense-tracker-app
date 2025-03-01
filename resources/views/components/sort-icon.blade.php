@props(['direction'])

@if($direction === 'asc')
    <x-icon name="sort-asc" class="w-3 h-3 inline-block ml-1" />
@elseif($direction === 'desc')
    <x-icon name="sort-desc" class="w-3 h-3 inline-block ml-1" />
@endif 