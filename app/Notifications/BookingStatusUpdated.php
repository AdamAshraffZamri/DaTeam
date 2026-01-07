<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

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
        
        // Format Dates professionally (e.g., 05 Jan 2026, 10:30 AM)
        $pickup = Carbon::parse($this->booking->originalDate . ' ' . $this->booking->bookingTime)->format('d M Y, h:i A');
        $dropoff = Carbon::parse($this->booking->returnDate . ' ' . $this->booking->returnTime)->format('d M Y, h:i A');
        
        // Determine color based on status (Optional visual cue)
        $color = match($this->booking->bookingStatus) {
            'Confirmed', 'Active', 'Completed' => 'success',
            'Cancelled', 'Rejected' => 'error',
            default => 'primary',
        };

        return (new MailMessage)
            ->subject("Update: Booking #{$this->booking->bookingID} is {$statusUpper}")
            ->greeting('Dear ' . ($notifiable->fullName ?? 'Valued Customer') . ',')
            ->line($this->message)
            ->line('') // Spacing
            ->line('**Reservation Summary**')
            ->line('🚗 **Vehicle:** ' . ($this->booking->vehicle->model ?? 'Vehicle') . ' (' . ($this->booking->vehicle->plateNo ?? 'N/A') . ')')
            ->line('📍 **Pickup:** ' . $pickup . ' (' . $this->booking->pickupLocation . ')')
            ->line('📍 **Return:** ' . $dropoff . ' (' . $this->booking->returnLocation . ')')
            ->line('💰 **Total Cost:** RM ' . number_format($this->booking->totalCost, 2))
            ->line('📊 **Current Status:** ' . $this->booking->bookingStatus)
            ->line('')
            ->action('View Booking Details', url('/bookings'))
            ->line('If you have any questions or require further assistance, please do not hesitate to contact our support team.')
            ->salutation('Best regards,' . "\n" . 'The DaTeam Management');
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