@props(['label', 'name', 'value' => null])

<div>
    <x-label :for="$name" :value="$label" />
    <div class="mt-1">
        <x-textarea
            :name="$name"
            :id="$name"
            {{ $attributes }}
        >{{ $value }}</x-textarea>
    </div>
    @error($name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div> 