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
        $logWhere = MedicationLogs::where('user_id',$userId)->first();
        $userWhere = User::Where('id',$userId)->first();
        $statWhere = UserStat::where('user_id',$userId)->first();

        $weeklySummary = [];
        $today = Carbon::now();

        if ($logWhere){

            $actualLogDates = $logWhere->where('user_id',$userId)->whereBetween('log_date',[Carbon::now()->startOfWeek(),Carbon::now()->endOfWeek()])
                ->get()
                ->pluck('log_date')
                ->toArray();
        } else {
            return response()->json([
                'status'=>'error',
                'message'=>'No medication logs found, please confirm medication first'
            ],404);
        }

        $startWeek = Carbon::now()->startOfWeek();
        $status = 'none';

        for ($i = 0; $i < 7; $i++){

            $dateLoop = $today->startOfWeek()->addDay($i)->format('Y-m-d');
            $todayCheck = Carbon::now()->format('Y-m-d');
            
            if (in_array($dateLoop,$actualLogDates) && $dateLoop <= $todayCheck){
                $status = 'taken';
            }else if ($dateLoop <= $todayCheck) {
                $status = 'missed';
            }else {
                $status = 'none';
            }

            $weeklySummary[] = [
                'date'=>$dateLoop,
                'status'=>$status,
            ];
        }

        $recentLogs = $logWhere->where('user_id',$userId)->orderBy('log_date','desc')->take(7)->get()->map(function($log){
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
                'highest_streak'=>$statWhere->highest_streak,
                'weekly_summary'=> $weeklySummary,
                'recent_logs'=>$recentLogs
            ]
        ],200);
    }

    public function WeeklyLog(Request $request, $week_start){
        $request->user()->currentAccessToken();
        
        $userId = $request->user()->id;
        $logWhere = MedicationLogs::where('user_id',$userId)->first();

        try{
            $startDate = Carbon::parse($week_start);

        }catch (\Exception $e){

            return response()->json([
                'status'=>'error',
                'message'=>'Please input a valid date within (Y-m-d) format'
            ],400);
        }

        $logs = [];
        $endDate = $startDate->copy()->addDay(7);

        if ($logWhere){

            $actualLogDates = $logWhere->where('user_id',$userId)->whereBetween('log_date',[$startDate, $endDate])
                ->get()
                ->pluck('log_date')
                ->toArray();
        }else {
            return response()->json([
                'status'=>'error',
                'message'=>'No medication logs found, please confirm medication first'
            ],404);
        }

        for ($i = 0; $i < 7; $i++){
            $dateLoop = $startDate->copy()->addDay($i);
            $dateCheck = $dateLoop->format('Y-m-d');

            if (in_array($dateCheck,$actualLogDates) && $dateLoop->lte(Carbon::now())){
                $status = 'taken';
            }else if ($dateLoop->lte(Carbon::now())){
                $status = 'missed';
            }else {
                $status = 'none';
            }

            $logs[]=[
                'date'=>$dateLoop->format('Y-m-d'),
                'day'=>$dateLoop->dayName,
                'status'=>$status,
            ];

        }

        $weekRange = $startDate->format('d M') . ' - ' . $dateLoop->format('d M');

        return response()->json([
            'status'=>'succes',
            'data'=>[
                'week_range'=>$weekRange,
                'logs'=>$logs
            ]
        ],200);
    }
}
