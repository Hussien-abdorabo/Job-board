<?php

namespace App\Jobs;

use App\Events\ApplicationStatusUpdated;
use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateApplicationStatusJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue , Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $applicationID;
    public $status;
    public function __construct($applicationID, $status)
    {
        $this->applicationID = $applicationID;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $application =Application::findorfail($this->applicationID);
        $old_status = $application->status;
        $application->update([
            'status' => $application->status,
        ]);
        Log::info("Application Status Updated",[
            'job_id' => $application->id,
            'user_id' => $application->user_id,
            'application_id'=> $application->id,
            'old_status' => $old_status,
            'status' => $this->status,
        ]);
        Log::info('Firing ApplicationStatusUpdated event', ['application_id' => $application->id]);
        event(new ApplicationStatusUpdated($application));


    }
}
