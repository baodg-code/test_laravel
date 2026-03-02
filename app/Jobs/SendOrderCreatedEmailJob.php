<?php

namespace App\Jobs;

use App\Mail\OrderCreatedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::query()
            ->with(['user:id,name,email', 'items'])
            ->find($this->orderId);

        if (! $order || ! $order->user?->email) {
            return;
        }

        Mail::to($order->user->email)->send(new OrderCreatedMail($order));
    }
}
