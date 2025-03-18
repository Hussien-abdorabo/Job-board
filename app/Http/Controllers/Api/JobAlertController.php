<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobAlertController extends Controller
{
    public function subscribeToAlerts(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'job_seeker'){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), [
            'location'=>'required|string|max:255',
            'type'=>'required|string|max:255',
        ]);
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }
        $JobAlert = JobAlert::create([
            'user_id'=> auth()->id(),
            'location'=> $request->location,
            'type'=> $request->type,
        ]);
        return response()->json(['success' => 'Job Alert created successfully'], 201);

    }
}
