<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $statusUpper = strtoupper($this->booking->bookingStatus);
        
        return (new MailMessage)
            ->subject("Booking #{$this->booking->bookingID}: {$statusUpper}")
            ->greeting('Hello ' . ($notifiable->fullName ?? 'Customer') . ',') // Customer has 'fullName'
            ->line($this->message)
            ->line('**Booking Details:**')
            ->line('Vehicle: ' . ($this->booking->vehicle->model ?? 'Vehicle'))
            ->line('Status: ' . $this->booking->bookingStatus)
            ->action('View Details', url('/bookings'))
            ->line('Thank you for choosing DaTeam!');
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