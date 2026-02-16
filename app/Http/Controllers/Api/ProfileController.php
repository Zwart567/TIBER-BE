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
            'control_freq_unit' => 'nullable|in:day,week,month',
            'next_checkup_date' => 'nullable|date'
        ]);

        $user = $request->user();

        $personalization = $user->personalization()->firstOrCreate([
            'user_id' => $user->id
        ]);

        if ($request->has('reminder_time')
            && $request->reminder_time != null) {

            $personalization->update(['reminder_time' => $request->reminder_time]);
            $updateData['reminder_time'] = $request->reminder_time;
        }

        if ($request->has('time_category')
            && $request->time_category != null) {

            $personalization->update(['time_category' => $request->time_category]);
            $updateData['time_category'] = $request->time_category;
        }

        if ($request->has('last_checkup_date')
            && $request->last_checkup_date != null) {

            $personalization->update(['last_checkup_date' => $request->last_checkup_date]);
            $updateData['last_checkup_date'] = $request->last_checkup_date;
        }

        if ($request->has('control_freq_value')
            && $request->control_freq_value != null) {

            $personalization->update(['control_freq_value' => $request->control_freq_value]);
            $updateData['control_freq_value'] = $request->control_freq_value;
        }

        // king aku nambahin statement nge check next checkup date, soalnya kalau ini diubah value, sama unit jadi diitung ulang
        if ($request->has('control_freq_unit')
            && $request->control_freq_unit != null
            && !$request->has('next_checkup_date')) {

            $personalization->update(['control_freq_unit' => $request->control_freq_unit]);
            $updateData['control_freq_unit'] = $request->control_freq_unit;
        }

        if (
            $personalization->last_checkup_date ||
            $personalization->control_freq_value ||
            $personalization->control_freq_unit
        ) {
            $dateCheck = Carbon::parse($personalization->last_checkup_date);

            if ($personalization->control_freq_unit == 'month') {
                $nextDate = $dateCheck->copy()->addDays(30 * $personalization->control_freq_value);
            } else if ($personalization->control_freq_unit == 'week') {
                $nextDate = $dateCheck->copy()->addDays(7 * $personalization->control_freq_value);
            } else {
                $nextDate = $dateCheck->copy()->addDays($personalization->control_freq_value);
            }

            $personalization->update([
                'next_checkup_date' => $nextDate,
            ]);

            $updateData['next_checkup_date'] = $nextDate->format('Y-m-d');
        }

        if ($request->has('next_checkup_date') && $request->next_checkup_date != null) {
            $lastCheck = Carbon::parse($personalization->last_checkup_date);
            $nextCheck = Carbon::parse($request->next_checkup_date);

            if ($nextCheck->gt($lastCheck)){
                $daysCheck = $lastCheck->diffInDays($nextCheck);

                // fmod ngehasilin nilai desimal kalau nilainya desimal, kalau nilai bulet nanti hasilnya 0
                if (fmod($daysCheck/30,1) == 0){
                    $personalization->update(['control_freq_unit' => 'month','control_freq_value'=> $daysCheck/30]);
                    $updateData['control_freq_unit'] = 'month';
                    $updateData['control_freq_value'] = $daysCheck/30;
                } else if (fmod($daysCheck/7,1) == 0){
                    $personalization->update(['control_freq_unit' => 'week','control_freq_value'=> $daysCheck/7]);
                    $updateData['control_freq_unit'] = 'week';
                    $updateData['control_freq_value'] = $daysCheck/7;
                }else {
                    $personalization->update(['control_freq_unit' => 'day','control_freq_value'=> $daysCheck]);
                    $updateData['control_freq_unit'] = 'day';
                    $updateData['control_freq_value'] = $daysCheck;
                }

                $personalization->update(['next_checkup_date' => $request->next_checkup_date]);
                $updateData['next_checkup_date'] = $request->next_checkup_date;
            }else {
                return response()->json([
                    'status'=>'error',
                    'message'=>'next_checkup_date cannot be less or equal to last_checkup_date'
                ],400);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Reminder Update',
            'data'    => $updateData,
        ], 200);
    }
}
