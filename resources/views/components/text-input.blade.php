@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm']) }}>
