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
        if ($request->input('personalization.control_freq_unit') == 'months'){
            $nextCheckupDate = $startDate->addDays(30);
        }else if ($request->input('personalization.control_freq_unit') == 'weeks'){
            $nextCheckupDate = $startDate->addDays(7);
        }else {
            $nextCheckupDate = $startDate->addDays(1);
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


    // ------------------------------------------------------------------------------
    // BAGIAN DASHBOARD
    // ------------------------------------------------------------------------------
    //NOTE : nanti tambahkan edge casing kalau semisalkan tanggalnya kurang dari seharusnya buat medication logs

    public function dashboard(Request $request){
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userStatWhere = UserStat::where('user_id', $userId)->first();
        $personalizationWhere= Personalization::where('user_id', $userId)->first();
        $logWhere = MedicationLogs::where('user_id', $userId)->first();

        $currentStreak = $userStatWhere->current_streak;

        $totalDays = 180; // Tanyakan ke team apakah ini dinamis atau tidak
        $daysPassed = 0;
        $startDate = Carbon::parse($personalizationWhere->start_date);
        $today = Carbon::now();
        if ($totalDays != $daysPassed){
            //
            $daysPassed = $startDate->diffInDays($today);
        }else{
            // Tanyakan ke team apakah perlu edge casing kalau sudah lebih dari total days
            $daysPassed = $totalDays;
        }

        $nextCheckup = $personalizationWhere->next_checkup_date;

        $isTakenToday = false;
        if ($userStatWhere->last_taken_date){
            $lastTaken = Carbon::parse($userStatWhere->last_taken_date);
            $isTakenToday = $lastTaken->isSameDay($today);
        }

        return response()->json([
            'status'=>'success',
            'data' => [
                'current_streak' => $currentStreak, 
                'days_passed' => (int) $daysPassed,
                'total_days' => $totalDays,
                'is_taken_today' => $isTakenToday
            ]

        ],200);
    }

    public function log(Request $request){

        $request->user()->currentAccessToken();
        
        $request->validate([
            'log_date' => 'required|date',
            'logged_time' => 'required',
        ]);

        MedicationLogs::create([
            'user_id' => $request-> user()-> id,
            'log_date' => $request -> input('log_date'),
            'logged_time' => $request -> input('logged_time')
        ]);


        // Mengubah data di user_stats karena API ini dikirim pas confirm medication 
        $userId = $request -> user() -> id;
        $userStatWhere = UserStat::where('user_id', $userId)->first();

        if ($userStatWhere){
            
            $inputDate = Carbon::parse($request -> input('log_date'));
            $currentDate = Carbon::parse($userStatWhere -> last_taken_date);
            // $isValid = ;

            if ($inputDate->isNextDay($currentDate)){

                $userStatWhere -> last_taken_date = $request -> input('log_date');
                
                if ($userStatWhere->highest_streak == $userStatWhere->current_streak ){
                    $userStatWhere -> current_streak += 1;
                    $userStatWhere->highest_streak = $userStatWhere -> current_streak;   
                }else{
                    $userStatWhere -> current_streak += 1;
                }

            // else runs if it were null or isn't next day
            }else {
                $userStatWhere -> last_taken_date = $request -> input('log_date');
                $userStatWhere -> current_streak = 1;
            }

            $userStatWhere->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => "Medication logged successfully",
            'data' => [
                'new_streak' => $userStatWhere->current_streak,
                'highest_streak' => $userStatWhere->highest_streak,
            ]
        ],200);

    }
}
