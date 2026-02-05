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

        $startDate = Carbon::parse($request->input('personalization.start_date'));
        $nextCheckupDate = $startDate->copy();
        if ($request->input('personalization.control_freq_unit') == 'months'){
            $nextCheckupDate = $nextCheckupDate->addDays(30*$request->input('personalization.control_freq_value'));
        }else if ($request->input('personalization.control_freq_unit') == 'weeks'){
            $nextCheckupDate = $nextCheckupDate->addDays(7*$request->input('personalization.control_freq_value'));
        }else {
            $nextCheckupDate = $nextCheckupDate->addDays(1*$request->input('personalization.control_freq_value'));
        }

        Personalization::create([
            'user_id'=> $user->id,
            'start_date'=> $startDate,
            'duration_month'=> $request->input('personalization.duration_month'),
            'control_freq_value'=> $request->input('personalization.control_freq_value'),
            'control_freq_unit'=> $request->input('personalization.control_freq_unit'),
            'reminder_time'=> $request->input('personalization.reminder_time'),
            'time_category'=> $request->input('personalization.time_category'),
            
            // Kayaknya last checkup ngambil dari start date aja sebagai inisialisasi
            'last_checkup_date' => $request->input('personalization.start_date'),
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


    

    // ----------------------------------------------------------------------------------------------------------
    // BAGIAN PROFILE & SETTINGS
    // ----------------------------------------------------------------------------------------------------------

    public function user(Request $request){
        
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();

        $request->validate([
            'fullname'=>'required|string',
            'old_password'=>'required',
            'new_password'=>'required',

        ]);
        

        $oldPassword = Hash::make($request -> input('old_password'));
        $newPassword = Hash::make($request -> input('new_password'));

        // ! $user || ! Hash::check($request->password, $user->password)
        if (Hash::check($request->old_password,$userWhere->password) && $oldPassword != $newPassword){
            
            $userWhere -> password = $newPassword;
            $userWhere -> fullname = $request->input('fullname');
            $userWhere->save();
        
        }else if ($oldPassword == $newPassword){
            return response()->json([
              'status' => 'error',
              'message' => 'The Old password and the new password is the same.'
            ],400);
        }
        else{
            return response()->json([
              'status' => 'error',
              'message' => 'The old password you entered is incorrect.'
            ],400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated',
        ],200);
    }
}
