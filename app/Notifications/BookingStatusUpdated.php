<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification
{
    use Queueable;

    public $booking;
    public $message;

    public function __construct(Booking $booking, $message)
    {
        $this->booking = $booking;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->bookingID,
            'status' => $this->booking->bookingStatus,
            'message' => $this->message,
        ];
    }
}