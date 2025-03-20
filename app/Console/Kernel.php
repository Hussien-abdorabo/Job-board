<?php

namespace App\Console;

use App\Models\Interview;
use App\Notifications\InterviewReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Define scheduled tasks here
        $schedule->call(function () {
            $now = now();
            $windowStart = $now->copy()->addHours(23);
            $windowEnd = $now->copy()->addHours(25);

            $interviews = Interview::whereBetween('scheduled_at', [$windowStart, $windowEnd])
                ->where('status', 'pending')
                ->where('reminder_sent', false)
                ->get();

            \Log::info('Scheduler ran', [
                'now' => $now->toDateTimeString(),
                'window_start' => $windowStart->toDateTimeString(),
                'window_end' => $windowEnd->toDateTimeString(),
                'interviews_count' => $interviews->count(),
            ]);

            foreach ($interviews as $interview) {
                if ($interview->jobSeeker) {
                    $interview->jobSeeker->notify(new InterviewReminder($interview));
                    $interview->update(['reminder_sent' => true]);
                } else {
                    \Log::warning('Interview reminder skipped: Job seeker not found', ['interview_id' => $interview->id]);
                }
            }
        })->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
