<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProductReviewed extends Notification
{
    use Queueable;

    

    /**
     * Create a new notification instance.
     */
    public $review;

    public function __construct($review)
    {
        $this->review = $review;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'review' => $this->review,
            'message' => 'A new review has been posted for your product!'
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'product_id' => $this->review->product_id,
            'review' => $this->review->content,
            'message' => 'A new review has been posted for your product.',
        ];
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'review' => $this->review,
            'message' => 'A new review has been posted for your product!',
        ];
    }
}
