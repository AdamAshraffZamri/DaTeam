<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewBookingSubmitted extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        // For now, we use 'database'. Add 'mail' later if your SMTP is set up.
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->bookingID,
            'customer_name' => $this->booking->customer->fullName ?? 'Guest',
            'message' => 'New booking #' . $this->booking->bookingID . ' submitted for ' . ($this->booking->vehicle->model ?? 'Vehicle'),
        ];
    }
}