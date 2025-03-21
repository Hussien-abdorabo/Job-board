<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendJobAlert;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $CacheKey = 'job_all'. md5($request->fullUrl());
        $jobs = Cache::remember($CacheKey,3600,function () use($request){
            $query = Job::with('user');

            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->input('location') . '%');
            }

            // Filter by salary range
            if ($request->has('min_salary')) {
                $query->where('salary', '>=', $request->input('min_salary'));
            }
            if ($request->has('max_salary')) {
                $query->where('salary', '<=', $request->input('max_salary'));
            }

            // Filter by job type
            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
            }
            return $query->get()->toArray();
        });
        return response()->json([
            'message' => 'Jobs retrieved successfully.',
            'data' => $jobs
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();


        if(!$user || $user->role !== 'employer'){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), rules: array(
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary' => 'required|integer',
            'experience' => ['required',Rule::in(Job::$experiences)],
            'type' => ['required',Rule::in(Job::$type)],
            'category' => ['required',Rule::in(Job::$categories)],
            'application_deadline' => 'required|date|after:today',
        ));
        if ($validated->fails()) {
            return response()->json([
                'errors' => $validated->errors(),
            ],422);
        }
        $job = Job::create([
            'user_id' => auth()->id(),
            'title'=>$request->title,
            'description'=>$request->description,
            'location'=>$request->location,
            'salary'=>$request->salary,
            'experience'=>$request->experience,
            'type'=>$request->type,
            'category'=>$request->category,
            'application_deadline'=>$request->application_deadline,
        ]);
        Cache::forget('jobs_all');
        SendJobAlert::dispatch($job);
        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job,
        ],201);

    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $user = auth()->user();
        if(!$user || $user->id !== $job->user_id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $Job = Job::where('id',$job->id)->first();
        $Job->delete();
        Cache::forget('jobs_all');
        return response()->json(['message' => 'Job deleted successfully'],200);
    }
}
