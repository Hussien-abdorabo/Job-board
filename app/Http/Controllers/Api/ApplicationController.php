<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\Models\Job;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
            ]);
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
        ]);
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
