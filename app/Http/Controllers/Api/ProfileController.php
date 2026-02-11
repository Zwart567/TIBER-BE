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

        // if ($request->filled('new_password') && $request->filled('old_password')){

        //     if (hash::check($request->old_password, $userWhere->password)){
        //        return response()->json([
        //           'status' => 'error',
        //           'message' => 'The old password you entered is incorrect.'
        //         ],400);
        //     }
        //     else if (hash::check($request->input('new_password'), $userWhere->password)){
        //         return response()->json([
        //           'status' => 'error',
        //           'message' => 'The old password and the new password is the same.'
        //         ],400);
        //     }

        //     $userWhere->password = Hash::make($request->input('new_password'));

        // }



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

        // $oldPassword = Hash::make($request -> input('old_password'));
        // $newPassword = Hash::make($request -> input('new_password'));

        // if (Hash::check($request->old_password,$userWhere->password) && $oldPassword != $newPassword){

        //     $userWhere -> password = $newPassword;
        //     $userWhere -> fullname = $request->input('fullname');
        //     $userWhere->save();

        // }else if ($oldPassword == $newPassword){
        //     return response()->json([
        //       'status' => 'error',
        //       'message' => 'The Old password and the new password is the same.'
        //     ],400);
        // }
        // else{
        //     return response()->json([
        //       'status' => 'error',
        //       'message' => 'The old password you entered is incorrect.'
        //     ],400);
        // }


    }

    // Update Medication & checkup reminder (Personalization)
    public function updatePersonalization(Request $request)
    {
        $request->validate([
            'reminder_time' => 'required|date_format:H:i:s',
            'time_category' => 'required|string',
            'last_checkup_date' => 'required|date',
            'control_freq_value' => 'required|integer|min:1',
            'control_freq_unit' => 'required|in:days,weeks,months'
        ]);

        $user = $request->user();

        $personalization = $user->personalization()->firstOrCreate([
            'user_id' => $user->id
        ]);

        $lastCheckup = Carbon::parse($request->last_checkup_date);
        $nextCheckup = match ($request->control_freq_unit) {
            'days' => $lastCheckup->copy()->addDays($request->control_freq_value),
            'weeks' => $lastCheckup->copy()->addWeeks($request->control_freq_value),
            'months' => $lastCheckup->copy()->addMonths($request->control_freq_value),
        };

        $personalization->update([
            'reminder_time' => $request->reminder_time,
            'time_category' => $request->time_category,
            'last_checkup_date' => $request->last_checkup_date,
            'control_freq_value' => $request->control_freq_value,
            'control_freq_unit' => $request->control_freq_unit,
            'next_checkup_date' => $nextCheckup->toDateString(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reminder Update',
            'data' => [
                'next_chechkup_date' => $nextCheckup->toDateString(),
            ]
        ], 200);
    }
}
