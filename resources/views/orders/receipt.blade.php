<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} {{ $order->receipt_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased bg-[#F7F0E3] min-h-screen py-10 px-4">
    <div class="max-w-sm mx-auto">
        <div class="no-print flex items-center justify-between mb-4">
            <a href="{{ route('orders.show', $order) }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                &larr; {{ __('Back to Order') }}
            </a>
            <div class="flex gap-3">
                <button onclick="window.print()" class="text-sm font-medium rounded-md px-3 py-1.5 bg-[#8A3330] hover:bg-[#742927] text-white">
                    {{ __('Print') }}
                </button>
                <a href="{{ route('orders.receipt.pdf', $order) }}" class="text-sm font-medium rounded-md px-3 py-1.5 border border-[#8A3330] text-[#8A3330] hover:bg-[#8A3330]/5">
                    {{ __('Download PDF') }}
                </a>
            </div>
        </div>

        <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 font-mono text-sm relative">
            @if ($order->payment_status === \App\Enums\PaymentStatus::Voided)
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="text-red-600/30 text-5xl font-black uppercase tracking-widest -rotate-12 border-4 border-red-600/30 px-4 py-1">
                        {{ __('Voided') }}
                    </span>
                </div>
            @endif
            <div class="text-center">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="88 Hotspring Resort" class="h-14 w-14 rounded-full object-cover mx-auto mb-2">
                @endif
                <p class="font-semibold uppercase tracking-wide">88 Hotspring Resort Inc.</p>
                <p class="text-xs text-gray-500">{{ __('Official Receipt') }}</p>
                <p class="mt-2 text-[11px] text-gray-500 leading-snug">
                    #9061 National Highway, Bagong Kalsada,<br>
                    Calamba City, 4027 Laguna
                </p>
                <p class="text-[11px] text-gray-500">0917-874-7888 &bull; info@88hotspring.com</p>
            </div>

            <div class="mt-4 pt-4 border-t border-dashed border-[#D9CCBA] space-y-1 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Receipt No.') }}</span><span>{{ $order->receipt_number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Order No.') }}</span><span>{{ $order->orderNumber() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Date') }}</span><span>{{ $order->paid_at->format('M d, Y g:i A') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Table') }}</span><span>{{ $order->table->table_number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Cashier') }}</span><span>{{ $order->creator->name ?? __('Unknown') }}</span></div>
            </div>

            <div class="mt-4 pt-4 border-t border-dashed border-[#D9CCBA]">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-2">
                        <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                        <span>{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-dashed border-[#D9CCBA] space-y-1">
                <div class="flex justify-between font-semibold"><span>{{ __('Total') }}</span><span>{{ number_format($order->total_amount, 2) }}</span></div>
                <div class="flex justify-between text-xs text-gray-500"><span>{{ __('Payment Method') }}</span><span class="uppercase">{{ $order->payment_method }}</span></div>
                <div class="flex justify-between text-xs text-gray-500"><span>{{ __('Amount Received') }}</span><span>{{ number_format($order->amount_received, 2) }}</span></div>
                <div class="flex justify-between text-xs text-gray-500"><span>{{ __('Change') }}</span><span>{{ number_format($order->change_amount, 2) }}</span></div>
            </div>

            @if ($order->payment_status === \App\Enums\PaymentStatus::Voided)
                <div class="mt-4 pt-4 border-t border-dashed border-red-300 space-y-1 text-xs">
                    <div class="flex justify-between font-semibold text-red-600"><span>{{ __('Status') }}</span><span>{{ __('VOIDED') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Void By') }}</span><span>{{ $order->voidedBy->name ?? __('Unknown') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Void Date') }}</span><span>{{ $order->voided_at?->format('M d, Y g:i A') }}</span></div>
                    <div class="flex justify-between gap-2"><span class="text-gray-500 shrink-0">{{ __('Reason') }}</span><span class="text-right">{{ $order->void_reason }}</span></div>
                </div>
            @endif

            <p class="mt-6 text-center text-xs text-gray-400">{{ __('Thank you for visiting 88 Hotspring Resort!') }}</p>
        </div>
    </div>
</body>
</html>
