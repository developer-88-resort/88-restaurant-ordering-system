<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#8A3330] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#742927] focus:bg-[#742927] active:bg-[#5f2120] focus:outline-none focus:ring-2 focus:ring-[#8A3330] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
