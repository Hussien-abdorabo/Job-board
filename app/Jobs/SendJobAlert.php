<?php

namespace App\Jobs;

use App\Models\JobAlert;
use App\Notifications\JobAlertNotification;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendJobAlert implements ShouldQueue
{
    use  Dispatchable, InteractsWithQueue,Queueable , SerializesModels;

    /**
     * Create a new job instance.
     */
    public  $jobmodel;
    public function __construct(Job $job)
    {
        $this->jobmodel = $job;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $alerts = JobAlert::where(function ($query) {
            $query->where('location', $this->jobmodel->location)
                ->orWhereNull('location');
        })->where(function ($query) {
            $query->where('type', $this->jobmodel->type)
                ->orWhereNull('type');
        })->get();

        foreach ($alerts as $alert) {
            $alert->user->notify(new JobAlertNotification($this->jobmodel));
        }

    }
}
