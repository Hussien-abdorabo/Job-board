<?php

namespace App\Http\Controllers\Api; // Adjust namespace based on error log

use App\Models\Application;
use App\Models\Interview;
use App\Models\InterviewFeedback;
use App\Notifications\InterviewStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterviewController
{
    public function store(Request $request)
    {
        $employer = auth()->user();

        $validator = Validator::make($request->all(), [
            'application_id' => 'required',
            'scheduled_at' => 'required|date|after:now',
            'text' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $application = Application::find($request->application_id);


        // Ensure the application has a job
        if (!$application->job) {
            return response()->json(['error' => 'Application has no associated job'], 400);
        }

//         Ensure the employer owns the job
        if ($employer->id !== $application->job->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the application has a valid user
        if (!$application->user) {
            return response()->json(['error' => 'Application has no associated job seeker'], 400);
        }

        $interview = Interview::create([
            'application_id' => $request->application_id,
            "employer_id" => $employer->id,
            'job_seeker_id' => $application->user_id,
            'scheduled_at' => $request->scheduled_at,
            'text' => $request->notes,
        ]);
        $interview->save();

        // Send the initial invitation email
//        $application->user->notify(new InterviewNotification($interview));

        return response()->json(['message' => 'Interview invitation sent', 'data' => $interview], 201);
    }
    public function update(Request $request, Interview $interview)
    {
        $user = auth()->user();

        if($user->id !== $interview->employer_id && $user->id !== $interview->job_seeker_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $status= Interview::find($interview->id);
//        dd($status);
        if (!$status) {
            return response()->json(['error' => 'Interview not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:accepted,rejected,canceled',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        if ($interview->status === 'rejected') {
            return response()->json(['error' => "you can't update canceled interview"], 400);
        }
        $status->update([
            'status' => $request->status,
        ]);
        if($user->id === $interview->employer_id){
            $interview->employer->notify(new InterviewStatusUpdated($status));
        }else{
            $interview->jobSeeker->notify(new InterviewStatusUpdated($status));
        }
        return response()->json(['message' => 'Interview status changed', 'data' => $status], 201);
    }

    public function index(Request $request)
    {
        $user=auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $query = Interview::where('employer_id',$user->id)
            ->orWhere('job_seeker_id',$user->id)
            ->with('employer','jobSeeker','application.job');
        if($request->has('status')){
            $query->where('status', $request->status);
        }
        $interviews = $query->get();
        return response()->json(['interview' => $interviews], 201);
    }

    public function show( Interview $interview)
    {
        $user=auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if($user->id !== $interview->employer_id && $user->id !== $interview->job_seeker_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }


        $interview->load(['employer','jobSeeker','application.job']);
        return response()->json(['InterviewDetails' => $interview], 201);
    }

    public function submitFeedback(Request $request, Interview $interview)
    {
        $validator = Validator::make($request->all(), [
            'feedback' => 'required|string|max:500',
            'rating' => 'required|numeric|between:1,5',
        ]);
        if ($validator->fails()) {
            return  response()->json(['error' => $validator->errors()], 422);
        }
        $user=auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if($user->id !== $interview->employer_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }

//        if($interview->feedback){
//            return response()->json(['error' => "Feedback already submitted for this interview"], 400);
//        }
        $interviewFeedback = InterviewFeedback::create([
            'interview_id'=>$interview->id,
            'employer_id'=>$user->id,
            'feedback' => $request->feedback,
            'rating' => $request->rating,
        ]);
        $interviewFeedback->save();
        return response()->json(['message' => 'Feedback submitted', 'data' => $interviewFeedback], 201);
    }
}




