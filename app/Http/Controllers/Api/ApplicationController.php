<?php

namespace App\Http\Controllers\Api;

use App\Events\ApplicationStatusUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateApplicationStatusJob;
use App\Models\Application;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Job;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if($user->role !=='employer'){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $jobId = Job::where('user_id',$user->id)->pluck('id');
        $applications = Application::whereIn('job_id',$jobId)
            ->with(['job','user'])
            ->paginate(10);
        return response()->json([
            'applications' => $applications
        ],200);
    }

public function jobSeekerApplication(Request $request)
{
    $user = auth()->user();
    if(!$user){
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    if($user->role !=='job_seeker'){
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $applications =Application::where('user_id',$user->id)
    ->with(['job','job.user'])
    ->get();
    return response()->json([
        'applications' => $applications
    ],200);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Job  $job)
    {
        $user = auth()->user();
        if(!$user || $user->role !== 'job_seeker'){
            return response()->json(['error' => 'only job_seeker can apply for jobs'], 401);
        }

        $validator = Validator::make($request->all(), [
            'resume_path'=>"required|mimes:pdf,doc,docx,csv,png,jpg,jpeg,svg|max:2048",
            'cover_letter'=>"nullable|string|max:200",
        ]);
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()->all(),
            ],422);
        }
        $resume_path =$request->file('resume_path')->store('resume','public');
        $application = Application::create([
            "user_id"=>auth()->id(),
            'job_id'=>$job->id,
            'resume_path'=>$resume_path,
            'cover_letter'=>$request->cover_letter,
        ]);

        return response()->json([
            'message' => 'Application Submitted successfully',
            'application'=>$application,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        $user = auth()->user();

        if(!$user || $user->role !== 'job_seeker'){
            return response()->json(['error' => 'only job_seeker can show their application status'], 401);
        }
        if ($user->id !== $application->user_id){
        $application = Application::where('id',$application->id)->first();
        if(!$application){
            return response()->json(['error' => 'Application not found'], 404);
        }
            return response()->json(['error' => 'you are not the owner of this applicaton'], 401);
        }

        return response()->json([
            'job' => $application->job,
            'application_status' => $application->status,
        ],201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Application $application)
    {
        Log::info("start update status",["application_id"=>$application->id]);
        $user = auth()->user();
        $job = $application->job;
        if (!$user || $user->role !=='employer') {
            return response()->json(['error' => 'only employer can update that'], 401);
        }
        if($user->id !== $job->user_id){
            return response()->json(['error' => 'you are not the employer who post this job of this job'], 401);
        }
        $validator = Validator::make($request->all(),rules: array(
            'status' => "required|",[Rule::in(Application::$statuses)]
        ));
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()->all(),
            ]);
        }
        UpdateApplicationStatusJob::dispatch($application->id,$request->status);
        return response()->json([
            'message' => 'Application Status Updated successfully',
            'status' => $request->status,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application)
    {
        $user = auth()->user();
        if(!$user || $user->id !== $application->user_id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $CanceledApplication = Application::where('id',$application->id)->first();
        if(!$CanceledApplication){
            return response()->json(['error' => 'Application not found'], 404);
        }
        $CanceledApplication->delete();
        return response()->json([
            'message' => 'Application canceled successfully',
        ]);
    }
}
