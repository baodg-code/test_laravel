@extends('emails.layouts.mail')

@section('title', 'Order Created')

@section('content')
    <h2>Your order has been created</h2>

    <p>Hello {{ $order->user->name }},</p>

    <p>
        We received your order
        <strong>{{ $order->order_number }}</strong>.
    </p>

    <p>Total: <strong>${{ number_format((float) $order->total, 2) }}</strong></p>

    <h3>Items</h3>
    <ul>
        @foreach ($order->items as $item)
            <li>
                {{ $item->product_name }}
                (x{{ $item->quantity }}) -
                ${{ number_format((float) $item->line_total, 2) }}
            </li>
        @endforeach
    </ul>

    <p>Thank you.</p>
@endsection
