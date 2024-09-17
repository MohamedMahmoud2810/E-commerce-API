<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;
    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'message' => 'Your order status has been updated to ' . $this->order->status,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'message' => 'Your order status has been updated to ' . $this->order->status,
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Order Status Updated')
                    ->line('Your order status has been updated to ' . $this->order->status)
                    ->action('View Order', url('/orders/' . $this->order->id));
    }
}
