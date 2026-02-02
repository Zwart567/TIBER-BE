<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Models\Personalization;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function login(Request $request)
    {
        $request->validate([
            'email'  => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'fullname' => $user->fullname,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'user.fullname'=>'required|string',
            'user.email'=>'required|email|unique:users,email',
            'user.password'=>'required',

            'personalization.start_date'=>'required|date',
            'personalization.duration_month'=>'required|integer',
            'personalization.control_freq_value'=>'required|integer',
            'personalization.control_freq_unit'=>'required|string',
            'personalization.reminder_time'=>'required',
            'personalization.time_category'=>'required|string',
        ]);

        $user = User::create([
            'fullname'=> $request->input('user.fullname'),
            'email'=> $request->input('user.email'),
            'password'=>Hash::make($request->input('user.password')),
        ]);

        Personalization::create([
            'user_id'=> $user->id,
            'start_date'=> $request->input('personalization.start_date'),
            'duration_month'=> $request->input('personalization.duration_month'),
            'control_freq_value'=> $request->input('personalization.control_freq_value'),
            'control_freq_unit'=> $request->input('personalization.control_freq_unit'),
            'reminder_time'=> $request->input('personalization.reminder_time'),
            'time_category'=> $request->input('personalization.time_category'),
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>'User and personalization created. Please login to continue'
        ],201);
    }
}
