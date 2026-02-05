<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\User;

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
            'fullname'=>'required|string',
            'old_password'=>'required',
            'new_password'=>'required',

        ]);
        

        $oldPassword = Hash::make($request -> input('old_password'));
        $newPassword = Hash::make($request -> input('new_password'));

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
