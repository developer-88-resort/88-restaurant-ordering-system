<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Order') }}
        </h2>
    </x-slot>

    @if ($tables->isEmpty())
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
            {{ __('Add a table first before creating orders.') }}
        </div>
    @elseif ($categories->isEmpty())
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
            {{ __('Add at least one available menu item first before creating orders.') }}
        </div>
    @else
        <form method="POST" action="{{ route('orders.store') }}"
              x-data="{
                  cart: [],
                  addItem(item) {
                      const existing = this.cart.find(line => line.id === item.id);
                      if (existing) {
                          existing.qty++;
                      } else {
                          this.cart.push({ id: item.id, name: item.name, price: item.price, qty: 1 });
                      }
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
                  get isEmpty() {
                      return this.cart.length === 0;
                  }
              }"
        >
            @csrf

            <div class="flex flex-col lg:flex-row gap-6 items-start">
                {{-- Menu browser --}}
                <div class="flex-1 w-full space-y-6">
                    <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                        <x-input-label for="table_id" :value="__('Table')" />
                        <select id="table_id" name="table_id" required
                                class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm">
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('table_id') == $table->id)>
                                    {{ $table->table_number }} ({{ $table->status->label() }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('table_id')" class="mt-2" />
                    </div>

                    @foreach ($categories as $category)
                        <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">{{ $category->name }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($category->menuItems as $item)
                                    <button type="button"
                                            @click="addItem({ id: {{ $item->id }}, name: {{ Js::from($item->name) }}, price: {{ $item->price }} })"
                                            class="flex items-center justify-between gap-3 text-left border border-[#E5DDD0] rounded-lg px-4 py-3 hover:border-[#8A3330] hover:bg-[#FAF6EE] transition">
                                        <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
                                        <span class="text-sm font-semibold text-[#8A3330] shrink-0">₱{{ number_format($item->price, 2) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Cart --}}
                <div class="w-full lg:w-96 shrink-0">
                    <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 lg:sticky lg:top-20">
                        <h3 class="font-semibold text-gray-900">{{ __('Order Summary') }}</h3>

                        <div class="mt-4 space-y-3" x-show="!isEmpty">
                            <template x-for="(line, index) in cart" :key="line.id">
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="line.name"></p>
                                            <p class="text-xs text-gray-500" x-text="'₱' + line.price.toFixed(2) + ' each'"></p>
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

                        <p class="mt-4 text-sm text-gray-400" x-show="isEmpty">{{ __('Tap menu items to add them to this order.') }}</p>

                        <div class="mt-4 pt-4 border-t border-[#E5DDD0] flex items-center justify-between" x-show="!isEmpty">
                            <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                            <span class="text-lg font-bold text-[#8A3330]" x-text="'₱' + total.toFixed(2)"></span>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes (optional)')" />
                            <textarea id="notes" name="notes" rows="2"
                                      class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('items')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <button type="submit" :disabled="isEmpty"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg font-semibold text-white bg-[#8A3330] hover:bg-[#742927] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                {{ __('Place Order') }}
                            </button>
                            <a href="{{ route('orders.index') }}" class="block mt-3 text-center text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</x-app-layout>
