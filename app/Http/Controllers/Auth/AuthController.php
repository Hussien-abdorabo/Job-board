<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use function MongoDB\Driver\Monitoring\removeSubscriber;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6|same:password',
            'type' => 'required|string|in:employee,job_seeker'
        ]);

        if( $validated->fails()){
            return response()->json([
                'errors' =>$validated->errors(),
            ],422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'token' => $token
        ],201);
    }

    // Login function
    public function login(Request $request){
        $validated = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if( $validated->fails()){
            return response()->json(['errors'=>$validated->errors()],422);
        }

        $user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            return response()->json([
                'errors'=>'invaild credentials'
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User successfully logged in',
            'user' => $user,
            'token' => $token
        ],200);
    }
}

