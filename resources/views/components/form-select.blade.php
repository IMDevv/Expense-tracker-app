@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null
])

<div>
    @if($label)
        <x-label :for="$name" :value="$label" />
    @endif
    <div class="mt-1">
        <select
            @if($name) name="{{ $name }}" id="{{ $name }}" @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
            ]) }}
        >
            <option value="">Select an option</option>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($value == $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    </div>
    @if($name)
        @error($name)
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror
    @endif
</div> 