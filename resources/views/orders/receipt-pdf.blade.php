<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans Mono', monospace; font-size: 11px; color: #222; margin: 0; padding: 16px; }
        .center { text-align: center; }
        .name { font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 13px; }
        .muted { color: #777; font-size: 10px; }
        .rule { border-top: 1px dashed #999; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .label { color: #777; }
        .total-row td { font-weight: bold; padding-top: 6px; }
        .logo { display: block; margin: 0 auto 6px auto; }
        .footer { text-align: center; color: #999; font-size: 10px; margin-top: 16px; }
        .address { color: #777; font-size: 9px; margin-top: 4px; line-height: 1.4; }
        .voided-banner { text-align: center; color: #c0392b; font-weight: bold; font-size: 13px; letter-spacing: 2px; border: 2px solid #c0392b; padding: 4px; margin-bottom: 10px; }
        .void-details td { color: #c0392b; }
        .void-details .label { color: #999; }
    </style>
</head>
<body>
    @if ($order->payment_status === \App\Enums\PaymentStatus::Voided)
        <div class="voided-banner">VOIDED</div>
    @endif
    <div class="center">
        @if (file_exists(public_path('images/logo.png')))
            <img class="logo" src="{{ public_path('images/logo.png') }}" width="56" height="56">
        @endif
        <p class="name">88 Hotspring Resort Inc.</p>
        <p class="muted">Official Receipt</p>
        <p class="address">
            #9061 National Highway, Bagong Kalsada,<br>
            Calamba City, 4027 Laguna<br>
            0917-874-7888 &bull; info@88hotspring.com
        </p>
    </div>

    <div class="rule"></div>

    <table>
        <tr><td class="label">Receipt No.</td><td class="right">{{ $order->receipt_number }}</td></tr>
        <tr><td class="label">Order No.</td><td class="right">{{ $order->orderNumber() }}</td></tr>
        <tr><td class="label">Date</td><td class="right">{{ $order->paid_at->format('M d, Y g:i A') }}</td></tr>
        <tr><td class="label">Table</td><td class="right">{{ $order->table->table_number }}</td></tr>
        <tr><td class="label">Cashier</td><td class="right">{{ $order->creator->name ?? 'Unknown' }}</td></tr>
    </table>

    <div class="rule"></div>

    <table>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->quantity }}x {{ $item->item_name }}</td>
                <td class="right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="rule"></div>

    <table>
        <tr class="total-row"><td>Total</td><td class="right">{{ number_format($order->total_amount, 2) }}</td></tr>
        <tr><td class="label">Payment Method</td><td class="right">{{ strtoupper($order->payment_method) }}</td></tr>
        <tr><td class="label">Amount Received</td><td class="right">{{ number_format($order->amount_received, 2) }}</td></tr>
        <tr><td class="label">Change</td><td class="right">{{ number_format($order->change_amount, 2) }}</td></tr>
    </table>

    @if ($order->payment_status === \App\Enums\PaymentStatus::Voided)
        <div class="rule"></div>
        <table class="void-details">
            <tr><td class="label">Status</td><td class="right">VOIDED</td></tr>
            <tr><td class="label">Void By</td><td class="right">{{ $order->voidedBy->name ?? 'Unknown' }}</td></tr>
            <tr><td class="label">Void Date</td><td class="right">{{ $order->voided_at?->format('M d, Y g:i A') }}</td></tr>
            <tr><td class="label">Reason</td><td class="right">{{ $order->void_reason }}</td></tr>
        </table>
    @endif

    <p class="footer">Thank you for visiting 88 Hotspring Resort!</p>
</body>
</html>
