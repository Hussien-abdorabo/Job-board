<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{

    public function sendMessage(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
            'content' => 'required|string|max:255',
        ]);
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }
        $application = Application::findorfail($request->application_id);
        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }
        if($user->id !== $application->user_id && $user->id !== $application->job->user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $receiverID = ($user->id === $application->user_id) ? $application->job->user_id : $application->user_id;
        $message = Message::create([
            'application_id' => $request->application_id,
            'sender_id'=>$user->id,
            'receiver_id' => $receiverID,
            'content'=> $request->get('content'),
        ]);
        broadcast(new NewMessage($message))->toOthers();
        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
            ], 200);
    }

    public function getMessages(Request $request, Application $application)
    {
        $user = auth()->user();
        $jobUserId = $application->job ? $application->job->user_id : null;
        \Log::info('Auth debug', [
            'user_id' => $user->id,
            'application_user_id' => $application->user_id,
            'job_user_id' => $jobUserId,
        ]);

        if ($user->id !== $application->user_id && ($jobUserId === null || $user->id !== $jobUserId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('application_id', $application->id)->get();

        return response()->json(['message' => 'Messages retrieved successfully', 'data' => $messages], 200);
    }
}
