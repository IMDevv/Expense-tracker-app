@props([
    'type' => 'text',
    'label' => null,
    'name' => null,
    'value' => null
])

<div>
    @if($label)
        <x-label :for="$name" :value="$label" />
    @endif
    <div class="mt-1">
        <input 
            type="{{ $type }}"
            @if($name) name="{{ $name }}" id="{{ $name }}" @endif
            @if($value) value="{{ $value }}" @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
            ]) }}
        >
    </div>
    @if($name)
        @error($name)
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror
    @endif
</div> 