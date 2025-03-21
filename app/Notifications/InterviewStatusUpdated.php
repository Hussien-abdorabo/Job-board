<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $status;
    public function __construct(Interview $interview)
    {
        //
        $this->status = $interview;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Interview Status Updated')
            ->line('The status of your interview has been updated to: ' . $this->status->status)
            ->line('Scheduled at: ' . $this->status->scheduled_at)
            ->line('Notes: ' . ($this->status->notes ?? 'N/A'))
            ->action('View Interview', url('/interviews/' . $this->status->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
            'interview_id' => $this->status->id,
            'status' => $this->status->status,
        ];
    }
}
