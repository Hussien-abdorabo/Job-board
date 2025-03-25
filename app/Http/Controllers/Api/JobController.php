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

/**
 * @OA\Info(
 *     title="Job Board API",
 *     version="1.0.0",
 *     description="API documentation for the Job Board application."
 * )
 */

class JobController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/jobs",
     *     operationId="getJobsList",
     *     tags={"Jobs"},
     *     summary="Get list of jobs",
     *     description="Returns a paginated list of jobs.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Software Engineer"),
     *                     @OA\Property(property="description", type="string", example="Develop and maintain software applications."),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-20T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-20T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
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
    /**
     * @OA\Post(
     *     path="/api/jobs",
     *     operationId="createJob",
     *     tags={"Jobs"},
     *     summary="Create a new job posting",
     *     description="Allows an authenticated user to create a new job posting. Invalidates the cache for the job listings.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "location", "salary"},
     *             @OA\Property(property="title", type="string", example="Software Engineer"),
     *             @OA\Property(property="description", type="string", example="We are looking for a skilled software engineer to join our team."),
     *             @OA\Property(property="location", type="string", example="Remote"),
     *             @OA\Property(property="salary", type="integer", example=80000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Job created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Software Engineer"),
     *                 @OA\Property(property="description", type="string", example="We are looking for a skilled software engineer to join our team."),
     *                 @OA\Property(property="location", type="string", example="Remote"),
     *                 @OA\Property(property="salary", type="integer", example=80000),
     *                 @OA\Property(property="status", type="string", example="open"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-20T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-20T12:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too Many Requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Too Many Requests")
     *         )
     *     )
     * )
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
    public function update(Request $request, Job $job)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($user->id !== $job->user_id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), rules: array(
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'salary' => 'nullable|integer',
            'experience' => ['nullable',Rule::in(Job::$experiences)],
            'type' => ['nullable',Rule::in(Job::$type)],
            'category' => ['nullable',Rule::in(Job::$categories)],
            'application_deadline' => 'nullable|date|after:today',
        ));
        if ($validated->fails()) {
            return response()->json([
                'errors' => $validated->errors(),
            ]);
        }
        $job->update([
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
        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job,
        ],201);
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
