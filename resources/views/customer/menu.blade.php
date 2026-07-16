<x-customer-layout :location-label="$space->area->name.' - '.$space->name">
    <div
        x-data="{
            cart: [],
            eachLabel: @js(__('each')),
            cartOpen: false,
            selectedCategory: 'all',
            selectCategory(id) {
                this.selectedCategory = id;
                const target = id === 'all' ? this.$refs.menuTop : document.getElementById('category-' + id);
                target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            },
            watchCategoryScroll() {
                const sections = [...this.$el.querySelectorAll('[data-category-id]')];
                if (!sections.length) return;

                // Active line sits just below the sticky header + chip bar.
                // The active category is the last section whose top has
                // already scrolled past that line — the standard scrollspy
                // rule, and unlike IntersectionObserver it stays correct in
                // both scroll directions and never flickers between two
                // categories when one section is short.
                const activeLine = 140;
                let ticking = false;

                const updateActiveCategory = () => {
                    let current = sections[0];

                    for (const section of sections) {
                        if (section.getBoundingClientRect().top - activeLine <= 0) {
                            current = section;
                        } else {
                            break;
                        }
                    }

                    this.selectedCategory = Number(current.dataset.categoryId);
                    ticking = false;
                };

                updateActiveCategory();

                window.addEventListener('scroll', () => {
                    if (ticking) return;
                    ticking = true;
                    window.requestAnimationFrame(updateActiveCategory);
                }, { passive: true });
            },
            centerChip(value) {
                const bar = this.$refs.chipBar;
                const chip = document.getElementById('chip-' + value);
                if (!bar || !chip) return;

                // Scroll only the chip strip itself (never scrollIntoView here —
                // it drags the whole page's scroll position too when the strip
                // is inside a position:sticky ancestor).
                bar.scrollTo({
                    left: chip.offsetLeft - (bar.clientWidth / 2) + (chip.clientWidth / 2),
                    behavior: 'smooth',
                });
            },
            addItem(item) {
                const existing = this.cart.find(line => line.id === item.id);
                if (existing) {
                    existing.qty++;
                } else {
                    this.cart.push({ id: item.id, name: item.name, price: item.price, qty: 1 });
                }
                this.cartOpen = true;
            },
            increment(id) {
                const line = this.cart.find(line => line.id === id);
                if (line) line.qty++;
            },
            decrement(id) {
                const line = this.cart.find(line => line.id === id);
                if (!line) return;
                line.qty--;
                if (line.qty <= 0) this.cart = this.cart.filter(l => l.id !== id);
            },
            get total() {
                return this.cart.reduce((sum, line) => sum + (line.price * line.qty), 0);
            },
            get count() {
                return this.cart.reduce((sum, line) => sum + line.qty, 0);
            },
            get isEmpty() {
                return this.cart.length === 0;
            }
        }"
        x-init="
            $nextTick(() => watchCategoryScroll());
            $watch('selectedCategory', (value) => centerChip(value));
        "
    >
        <form method="POST" action="{{ route('customer.orders.store', $space) }}">
            @csrf
            @if ($customerName)
                <input type="hidden" name="customer_name" value="{{ $customerName }}">
            @endif

            @if ($customerName)
                <p class="px-4 pt-3 text-sm text-[#8A7B6D] max-w-5xl mx-auto">{{ __('Hi :name! Here\'s the menu — add whatever you like.', ['name' => $customerName]) }}</p>
            @endif

            @if ($categories->isNotEmpty())
                <div class="sticky top-16 z-30 bg-[#F7F0E3]/95 backdrop-blur-sm border-b border-[#E5DDD0]">
                    <div x-ref="chipBar" class="max-w-5xl mx-auto px-4 py-2.5 flex items-center gap-2 overflow-x-auto no-scrollbar">
                        <button type="button"
                                id="chip-all"
                                @click="selectCategory('all')"
                                :class="selectedCategory === 'all' ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#E5DDD0] hover:border-[#8A3330]'"
                                class="shrink-0 whitespace-nowrap text-sm font-medium px-3.5 py-1.5 rounded-full border transition-colors">
                            {{ __('All') }}
                        </button>
                        @foreach ($categories as $category)
                            <button type="button"
                                    id="chip-{{ $category->id }}"
                                    @click="selectCategory({{ $category->id }})"
                                    :class="selectedCategory === {{ $category->id }} ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#E5DDD0] hover:border-[#8A3330]'"
                                    class="shrink-0 whitespace-nowrap text-sm font-medium px-3.5 py-1.5 rounded-full border transition-colors">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div x-ref="menuTop" class="px-4 py-6 pb-8 max-w-5xl mx-auto space-y-8 scroll-mt-32" :class="!isEmpty ? 'pb-28' : ''">
                @if ($categories->isEmpty())
                    <x-empty-state
                        :title="__('Nothing on the menu right now')"
                        :description="__('Please ask our staff for assistance with your order.')"
                    />
                @else
                    @foreach ($categories as $category)
                        <div id="category-{{ $category->id }}" data-category-id="{{ $category->id }}" class="scroll-mt-32">
                            <div class="flex items-center gap-2.5 mb-3">
                                <span class="h-5 w-1 rounded-full bg-[#8A3330]"></span>
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                <span class="text-xs font-medium text-[#8A7B6D] bg-[#F7F0E3] border border-[#E5DDD0] rounded-full px-2 py-0.5">{{ $category->menuItems->count() }}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($category->menuItems as $item)
                                    <button type="button"
                                            @click="addItem({ id: {{ $item->id }}, name: {{ Js::from($item->name) }}, price: {{ $item->price }} })"
                                            class="w-full flex items-center gap-3 text-left bg-white border border-[#E5DDD0] rounded-xl p-3 shadow-sm hover:border-[#8A3330] hover:shadow-md transition">
                                        <div class="h-16 w-16 rounded-lg bg-[#FAF6EE] shrink-0 overflow-hidden flex items-center justify-center">
                                            @if ($item->image_path)
                                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-6 w-6 text-[#D9CCBA]">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                                            <p class="text-sm font-semibold text-[#8A3330] mt-0.5">₱{{ number_format($item->price, 2) }}</p>
                                        </div>
                                        <span class="shrink-0 h-8 w-8 rounded-full bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Sticky bottom cart: full-width bar on mobile, floating centered card on desktop --}}
            <div x-show="!isEmpty" x-cloak class="fixed bottom-0 inset-x-0 z-40 sm:px-4 sm:pb-4">
                <div class="sm:max-w-xl sm:mx-auto sm:rounded-2xl sm:overflow-hidden sm:shadow-2xl sm:border sm:border-[#E5DDD0]">
                <div x-show="cartOpen" x-cloak @click.away="cartOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white border-t border-[#E5DDD0] rounded-t-2xl sm:rounded-t-none shadow-2xl sm:shadow-none max-h-[70vh] overflow-y-auto">
                    <div class="p-4 max-w-2xl mx-auto">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">{{ __('Your Order') }}</h3>
                            <button type="button" @click="cartOpen = false" class="text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(line, index) in cart" :key="line.id">
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="line.name"></p>
                                            <p class="text-xs text-gray-500" x-text="'₱' + line.price.toFixed(2) + ' ' + eachLabel"></p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="decrement(line.id)" class="h-7 w-7 rounded-full border border-[#D9CCBA] text-gray-600 hover:bg-gray-50">−</button>
                                            <span class="w-5 text-center text-sm font-medium" x-text="line.qty"></span>
                                            <button type="button" @click="increment(line.id)" class="h-7 w-7 rounded-full border border-[#D9CCBA] text-gray-600 hover:bg-gray-50">+</button>
                                        </div>
                                    </div>
                                    <input type="hidden" :name="'items[' + index + '][menu_item_id]'" :value="line.id">
                                    <input type="hidden" :name="'items[' + index + '][quantity]'" :value="line.qty">
                                </div>
                            </template>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes (optional)')" />
                            <textarea id="notes" name="notes" rows="2"
                                      class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('items')" class="mt-2" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="mt-4 pt-4 border-t border-[#E5DDD0] flex items-center justify-between">
                            <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                            <span class="text-lg font-bold text-[#8A3330]" x-text="'₱' + total.toFixed(2)"></span>
                        </div>

                        <button type="submit" :disabled="isEmpty"
                                class="w-full mt-4 inline-flex items-center justify-center px-4 py-3 rounded-lg font-semibold text-white bg-[#8A3330] hover:bg-[#742927] disabled:opacity-40 disabled:cursor-not-allowed transition">
                            {{ __('Place Order') }}
                        </button>
                    </div>
                </div>

                <button type="button" @click="cartOpen = !cartOpen"
                        class="w-full bg-[#8A3330] hover:bg-[#742927] text-white px-4 py-3.5 flex items-center justify-between shadow-2xl sm:shadow-none transition">
                    <span class="text-sm font-semibold" x-text="count + ' {{ __('items') }} · ₱' + total.toFixed(2)"></span>
                    <span class="text-sm font-semibold" x-text="cartOpen ? '{{ __('Close') }}' : '{{ __('View Order') }}'"></span>
                </button>
                </div>
            </div>
        </form>
    </div>
</x-customer-layout>
