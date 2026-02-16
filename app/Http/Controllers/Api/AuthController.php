<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

use App\Models\User;
use App\Models\Personalization;
use App\Models\MedicationLogs;
use App\Models\UserStat;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    // ------------------------------------------------------------------------------
    // BAGIAN LOGIN, LOGOUT, REGISTER
    // ------------------------------------------------------------------------------
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
                'message' => 'Invalid email or password'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
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
        
        $controlEnum = ['day','week','month'];
        $timeEnum = ['pagi','siang','sore','malam'];
        $checkControlEnum = $request->input('personalization.control_freq_unit');
        $checkTimeEnum = $request->input('personalization.time_category');

        if (!in_array($checkControlEnum,$controlEnum) || !in_array($checkTimeEnum,$timeEnum)){
            return response()->json([
                'status'=>'error',
                'message'=>'Please input the correct enum'
            ],400);
        }
        
        $user = User::create([
            'fullname'=> $request->input('user.fullname'),
            'email'=> $request->input('user.email'),
            'password'=>Hash::make($request->input('user.password')),
        ]);

        //
        $userWhere = User::where('email', $request->input('user.email'))->first();

        $startDate = Carbon::parse($userWhere->created_at);
        $nextCheckupDate = $startDate->copy();
        if ($request->input('personalization.control_freq_unit') == 'month'){
            $nextCheckupDate = $nextCheckupDate->addDays(30*$request->input('personalization.control_freq_value'));
        }else if ($request->input('personalization.control_freq_unit') == 'week'){
            $nextCheckupDate = $nextCheckupDate->addDays(7*$request->input('personalization.control_freq_value'));
        }else {
            $nextCheckupDate = $nextCheckupDate->addDays(1*$request->input('personalization.control_freq_value'));
        }

        Personalization::create([
            'user_id'=> $user->id,
            'start_date'=> $request->input('personalization.start_date'),
            'duration_month'=> $request->input('personalization.duration_month'),
            'control_freq_value'=> $request->input('personalization.control_freq_value'),
            'control_freq_unit'=> $request->input('personalization.control_freq_unit'),
            'reminder_time'=> $request->input('personalization.reminder_time'),
            'time_category'=> $request->input('personalization.time_category'),
            
            // Kayaknya last checkup ngambil dari start date aja sebagai inisialisasi
            'last_checkup_date' => $userWhere->created_at,
            'next_checkup_date' => $nextCheckupDate
        ]);

        // Kepikirannya buat inisialisasi si table migrationnya
        UserStat::create([
            'user_id'=> $user -> id,
            'current_streak' => 0,
            'highest_streak' => 0,
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>'User and personalization created. Please login to continue'
        ],200);
    }

}
