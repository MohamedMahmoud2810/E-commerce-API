<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class NewProductAdded extends Notification
{
    use Queueable;

    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'message' => 'A new product has been added: ' . $this->product->name,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'message' => 'A new product has been added: ' . $this->product->name,
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Product Added')
                    ->line('A new product has been added: ' . $this->product->name)
                    ->action('View Product', url('/products/' . $this->product->id));
    }
}
