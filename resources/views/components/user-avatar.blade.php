@props(['user', 'size' => 8])

@if($user->avatar)
    <img {{ $attributes->merge([
        'class' => "h-{$size} w-{$size} rounded-full object-cover",
        'src' => Storage::url($user->avatar),
        'alt' => $user->name
    ]) }} />
@else
    <div {{ $attributes->merge([
        'class' => "h-{$size} w-{$size} rounded-full bg-gray-700 flex items-center justify-center"
    ]) }}>
        <span class="text-gray-300 text-sm font-medium">
            {{ substr($user->name, 0, 1) }}
        </span>
    </div>
@endif 