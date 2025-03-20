<?php
namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewReminder extends Notification
{
    use Queueable;

    protected $interview;

    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Interview Reminder')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a reminder for your upcoming interview for the job: ' . $this->interview->application->job->title)
            ->line('Scheduled at: ' . $this->interview->scheduled_at->format('Y-m-d H:i'))
            ->line('Notes: ' . ($this->interview->notes ?? 'None'))
            ->action('View Application', url('/api/applications/' . $this->interview->application_id))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at->toDateTimeString(),
        ];
    }
}
