<?php
use App\Models\Application;

Broadcast::channel('chat.{applicationId}', function ($user, $applicationId) {
    $application = Application::find($applicationId);
    if (!$application) {
        return false;
    }
    return $user->id === $application->user_id || $user->id === $application->job->user_id;
});
