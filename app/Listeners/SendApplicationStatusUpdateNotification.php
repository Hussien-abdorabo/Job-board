<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Notifications\ApplicationStatusUpdateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApplicationStatusUpdateNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusUpdated $event): void
    {
        $jobSeeker = $event->application->user;
        $jobSeeker->notify(new ApplicationStatusUpdateNotification($event->application));
    }
}
