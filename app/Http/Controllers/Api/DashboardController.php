<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;

use App\Models\User;
use App\Models\Personalization;
use App\Models\MedicationLogs;
use App\Models\UserStat;

class DashboardController extends Controller
{
    // ------------------------------------------------------------------------------
    // BAGIAN DASHBOARD
    // ------------------------------------------------------------------------------
    //NOTE : nanti tambahkan edge casing kalau semisalkan tanggalnya kurang dari seharusnya buat medication logs

    public function dashboard(Request $request){
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();
        $userStatWhere = UserStat::where('user_id', $userId)->first();
        $personalizationWhere= Personalization::where('user_id', $userId)->first();
        $logWhere = MedicationLogs::where('user_id', $userId)->first();

        $currentStreak = $userStatWhere->current_streak;
        
        $totalDays = $personalizationWhere->duration_month*30; // Tanyakan ke team apakah ini dinamis atau tidak
        $daysPassed = 0;
        $today = Carbon::now();
        if ($totalDays != $daysPassed){
            $startDate = Carbon::parse($personalizationWhere->start_date);
            $daysPassed = abs($startDate->diffInDays($today));
        }else{
            // Tanyakan ke team apakah perlu edge casing kalau sudah lebih dari total days
            $daysPassed = $totalDays;
        }


        // semisalkan di hari-h +1 dia nambah sesuai control freq
        $nextCheckup = Carbon::parse($personalizationWhere->next_checkup_date);
        if (Carbon::now()->gt(Carbon::parse($personalizationWhere->next_checkup_date))){
            $freqUnit = $personalizationWhere->control_freq_unit;
            $freqValue = $personalizationWhere->control_freq_value;

            $personalizationWhere->update(['last_checkup_date' => $nextCheckup]);

            if ( $freqUnit == 'month'){
                $personalizationWhere->update(['next_checkup_date' => $nextCheckup->addDays(30*$freqValue)]);
            }else if ($freqUnit == 'week'){
                $personalizationWhere->update(['next_checkup_date' => $nextCheckup->addDays(7*$freqValue)]);
            }else {
                $personalizationWhere->update(['next_checkup_date' => $nextCheckup->addDays($freqValue)]);
            }
            // terkadang next_checkup_date data typenya bukan Carbon date
            $nextCheckup = $personalizationWhere->next_checkup_date->format('Y-m-d');
        }else {
            $nextCheckup = $personalizationWhere->next_checkup_date;
        }

        $isTakenToday = false;
        if ($userStatWhere->last_taken_date){
            $lastTaken = Carbon::parse($userStatWhere->last_taken_date);
            $isTakenToday = $lastTaken->isSameDay($today);
        }

        $durationMonth = $personalizationWhere->duration_month;

        return response()->json([
            'status'=>'success',
            'data' => [
                'duration_month' => $durationMonth,
                'current_streak' => $currentStreak, 
                'days_passed' => (int) $daysPassed,
                'total_days' => $totalDays,
                'next_checkup' => $nextCheckup,
                'is_taken_today' => $isTakenToday
            ]

        ],200);
    }

    public function log(Request $request){

        $request->user()->currentAccessToken();
        $userId = $request -> user() -> id;
        
        $request->validate([
            'log_date' => 'required|date|date_equals:today',
            'logged_time' => 'required',
        ]);

        
        $dateExisted = MedicationLogs::where('user_id',$userId)->where('log_date',$request->input('log_date'))->exists();
        
        if ($dateExisted){

            return response()->json([
                'status'=>'error',
                'message'=>'You have already confirmed medication for today.',
            ],400);
        }

        MedicationLogs::create([
                'user_id' => $request-> user()-> id,
                'log_date' => $request -> input('log_date'),
                'logged_time' => $request -> input('logged_time')
        ]);
        

        // Mengubah data di user_stats karena API ini dikirim pas confirm medication 
        $userStatWhere = UserStat::where('user_id', $userId)->first();

        if ($userStatWhere){
            
            $inputDate = Carbon::parse($request -> input('log_date'));
            $currentDate =  $userStatWhere -> last_taken_date ? Carbon::parse($userStatWhere -> last_taken_date) : null;

            $isStreak = false;
            if ($currentDate){
                $expectedNextDay = $currentDate->copy()->addDay();
                $isStreak = $inputDate->isSameDay($expectedNextDay);
            }

            if ($isStreak){
                $userStatWhere->current_streak += 1;
            }else {
                $isSameDay = $currentDate && $inputDate->isSameDay($currentDate);

                if (!$isSameDay){
                    $userStatWhere->current_streak = 1;
                }
            }

            if ($userStatWhere->current_streak > $userStatWhere->highest_streak){
                $userStatWhere->highest_streak = $userStatWhere->current_streak;
            }

            $userStatWhere->last_taken_date = $inputDate;
            $userStatWhere->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => "Medication logged successfully",
            'data' => [
                'new_streak' => $userStatWhere->current_streak,
                'highest_streak' => $userStatWhere->highest_streak,
            ],
        ],200);

    }
}
