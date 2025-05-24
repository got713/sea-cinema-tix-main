<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancellation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Cancelled - ' . $this->booking->movie->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been cancelled.')
            ->line('Movie: ' . $this->booking->movie->title)
            ->line('Date: ' . $this->booking->dateShowtime->date->date->format('M d, Y'))
            ->line('Time: ' . $this->booking->dateShowtime->showtime->start_time)
            ->line('Seats: ' . $this->booking->seats->pluck('seat_number')->implode(', '))
            ->line('Refund Amount: $' . $this->booking->total_price)
            ->line('The refund has been processed and will be credited to your account.')
            ->line('We hope to see you again soon!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'movie_title' => $this->booking->movie->title,
            'date' => $this->booking->dateShowtime->date->date->format('M d, Y'),
            'time' => $this->booking->dateShowtime->showtime->start_time,
            'seats' => $this->booking->seats->pluck('seat_number')->implode(', '),
            'refund_amount' => $this->booking->total_price,
        ];
    }
} 