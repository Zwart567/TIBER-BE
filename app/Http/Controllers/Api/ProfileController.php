<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\User;
use Carbon\Carbon;
use App\Models\Personalization;

class ProfileController extends Controller
{
    // ----------------------------------------------------------------------------------------------------------
    // BAGIAN PROFILE & SETTINGS
    // ----------------------------------------------------------------------------------------------------------

    public function user(Request $request){

        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();

        $request->validate([
            'fullname'=>'nullable|string|max:255',
            'old_password'=>'nullable|required_with:new_password',
            'new_password'=>'nullable|min:6|different:old_password',

        ]);


        if ($request->filled('fullname')){
            $userWhere->fullname = $request->input('fullname');
        }

        $oldPassword = $request -> input('old_password');
        $newPassword = $request -> input('new_password');

        if ($request->filled('new_password') && $request->filled('old_password')){
            if (Hash::check($request->old_password,$userWhere->password) && $oldPassword != $newPassword){

                $userWhere -> password = $newPassword;

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
        }

        $userWhere->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated',
        ],200);


    }

    // Update Medication & checkup reminder (Personalization)
    public function updatePersonalization(Request $request)
    {
        $request->validate([
            'reminder_time' => 'nullable|date_format:H:i:s',
            'time_category' => 'nullable|string|in:pagi,siang,sore,malam',
            'last_checkup_date' => 'nullable|date',
            'control_freq_value' => 'nullable|integer|min:1',
            'control_freq_unit' => 'nullable|in:day,week,month'
        ]);

        $user = $request->user();

        $personalization = $user->personalization()->firstOrCreate([
            'user_id' => $user->id
        ]);

        if ($request->filled('reminder_time')){
            $personalization->update(['reminder_time' => $request->reminder_time]);
        }

        if ($request->filled('time_category')){
            $personalization->update(['time_category' => $request->time_category]);
        }

        
        if ($request->filled('last_checkup_date')){
            $personalization->update(['last_checkup_date' => $request->last_checkup_date]);
        }

        if ($request->filled('control_freq_value')){
            $personalization->update(['control_freq_value' => $request->control_freq_value]);
        }

        $controlValue = $personalization->control_freq_value;
        if ($request->filled('control_freq_unit')){
            $personalization->update(['control_freq_unit' => $request->control_freq_unit]);   
        }

        $dateCheck = Carbon::parse($personalization->last_checkup_date);
        if ($request->control_freq_unit == 'month'){
            $personalization->update(['next_checkup_date'=>$dateCheck->addDays(30*$personalization->control_freq_value)]);
        }else if ($request->control_freq_unit == 'week'){
            $personalization->update(['next_checkup_date'=>$dateCheck->addDayss(7*$personalization->control_freq_value)]);
        }else {
            $personalization->update(['next_checkup_date'=>$dateCheck->addDays($personalization->control_freq_value)]);
        }

        

        return response()->json([
            'status' => 'success',
            'message' => 'Reminder Update',
            'data' => [
                'next_checkup_date' => $personalization->next_checkup_date->format('Y-m-d'),
            ]
        ], 200);
    }
}
