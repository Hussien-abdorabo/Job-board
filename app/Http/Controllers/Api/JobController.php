<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = Job::all();
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
    public function destroy(string $id)
    {
        //
    }
}
