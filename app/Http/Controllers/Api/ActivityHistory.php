<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;

use App\Models\User;
use App\Models\UserStat;
use App\Models\MedicationLogs;

class ActivityHistory extends Controller
{
    public function ActivityOverview(Request $request){

        $request->user()->currentAccessToken();

        $userId = $request->user()->id;
        // $userStatWhere = UserStat::where('user_id',$userId)->first();
        $logWhere = MedicationLogs::where('user_id',$userId)->first();
        $userWhere = User::Where('id',$userId)->first();

        $weeklySummary = [];

        $today = Carbon::now();
        $sevenDaysAgo = $today->copy()->subDay(7);
        
        $createdAt = Carbon::parse($userWhere->created_at)->format('Y-m-d');

        $actualLogDates = $logWhere->whereBetween('log_date',[$sevenDaysAgo,$today])->pluck('log_date')->toArray();

        for ($i = 0; $i < 7; $i++){

        }
        
        // took yesterday because today is supposedly on 
        for ($i = 0; $i < 7; $i++){
            $dateCheck = Carbon::now()->subDay($i)->format('Y-m-d');

            if ($dateCheck == $createdAt){
                $weeklySummary[] = [
                    'date'=>$dateCheck,
                    'status'=>$status,
                ];

                break;
            }else{
                if (in_array($dateCheck,$actualLogDates)){
                    $status = 'taken';
                }else {
                    $status = 'missed';
                }
            }
            
            $weeklySummary[] = [
                'date'=>$dateCheck,
                'status'=>$status,
            ];
        }
        
        $recentLogs = $logWhere->orderBy('log_date','desc')->take(1)->get()->map(function($log){
            return [
                'id'=>$log->id,
                'user_id'=>$log->user_id,
                'date'=>$log->log_date,
                'time'=>Carbon::parse($log->created_at)->format('H:i:s'),
                'status'=>'taken'
            ];

        });

        return response()->json([
            'status'=>'success',
            'data'=>[
                'weekly_summary'=> $weeklySummary,
                'recent_logs'=>$recentLogs
            ]
        ],200);
    }
}
