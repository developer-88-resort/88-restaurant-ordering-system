@props(['user'])

@if ($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" {{ $attributes->merge(['class' => 'rounded-full object-cover border border-[#E5DDD0]']) }}>
@else
    <div {{ $attributes->merge(['class' => 'rounded-full bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center font-semibold shrink-0']) }}>
        {{ $user->initials() }}
    </div>
@endif
