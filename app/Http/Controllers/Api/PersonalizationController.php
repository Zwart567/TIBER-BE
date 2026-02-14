<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;

use App\Models\Personalization;

class PersonalizationController extends Controller
{
    public function personalization(Request $request){
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $personalizationWhere = Personalization::where('user_id', $userId)->first();

        return response()->json([
            'status'=>'success',
            'data'=>[
                'start_date'=>$personalizationWhere->start_date,
                'duration_month'=>$personalizationWhere->duration_month,
                'control_freq_value'=>$personalizationWhere->control_freq_value,
                'control_freq_unit'=>$personalizationWhere->control_freq_unit,
                'last_checkup_date'=>$personalizationWhere->last_checkup_date,
                'next_checkup_date'=>$personalizationWhere->next_checkup_date,
                'reminder_time'=>$personalizationWhere->reminder_time,
                'time_category'=>$personalizationWhere->time_category,
            ]
        ],200);

    }
}
