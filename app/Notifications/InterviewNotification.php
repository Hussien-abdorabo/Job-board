<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $interview;
    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
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
            ->subject('Interview invitation')
            ->greeting('Hello, '.$notifiable->name)
            ->line('You have been invited to an interview for the job:' .$this->interview->application->job->title)
            ->line('Schedule at: '.$this->interview->scheduled_at->format('Y-m-d H:i'))
            ->action('view application', url('/'.$this->interview->application_id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' =>$this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at->toDateTimeString(),
        ];
    }
}
