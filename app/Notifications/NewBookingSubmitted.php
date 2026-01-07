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
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Booking #' . $this->booking->bookingID . ' Submitted')
            ->greeting('Hello ' . $notifiable->name . ',') // Staff has 'name'
            ->line('A new booking request has been received.')
            ->line('**Customer:** ' . ($this->booking->customer->fullName ?? 'Guest'))
            ->line('**Vehicle:** ' . ($this->booking->vehicle->model ?? 'Unknown') . ' (' . ($this->booking->vehicle->plateNo ?? '') . ')')
            ->line('**Dates:** ' . $this->booking->originalDate . ' to ' . $this->booking->returnDate)
            ->line('**Total Cost:** RM ' . number_format($this->booking->totalCost, 2))
            ->action('Review Booking', url('/staff/bookings/' . $this->booking->bookingID));
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