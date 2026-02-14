<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\CheckupNotes;
use App\Models\Personalization;

class CheckupNote extends Controller
{
    public function AddCheckupNote(Request $request){
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();
        $personalizationWhere = Personalization::where('user_id', $userId)->first();

        $request->validate([
            'status'=>'required|string|max:255',
            'color_status'=>'required|string',
            'notes'=>'required|string'
        ]);

        $warnaEnum = ['biru','hijau','kuning'];
        $checkEnum = $request->color_status;
        if ($request->filled('color_status')){

            if (!in_array($checkEnum, $warnaEnum)){
                return response()->json([
                    'status'=>'error',
                    'message'=>'Please input correct color_status biru, hijau , kuning'
                ],400);
            }
        }

        $checkup = CheckupNotes::create([
            'user_id'=>$userWhere->id,
            'status'=>$request->input('status'),
            'color_status'=>$request->input('color_status'),
            'notes'=>$request->input('notes'),
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>'Note succesfully saved'
        ],200);
    }

    public function GetCheckupNote(Request $request){
        $request->user()->currentAccessToken();

        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();
        $checkupWhere = CheckupNotes::where('user_id',$userId);
        
        if (!$checkupWhere->where('id',$userId)->first()){
            return response()->json([
                'status'=>'error',
                'message'=>'No checkup notes available'
            ],404);
        }

        $data = [];

        return response()->json([
            'status'=>'success',
            'data'=>$checkupWhere->get()
        ],200);
    }

    public function PutCheckupNote(Request $request){
        $request->user()->currentAccessToken();
        
        $userId = $request -> user() -> id;
        $userWhere = User::where('id', $userId)->first();   

        $request->validate([
            'id'=>'required|integer',
            'status'=>'string|max:255',
            'color_status'=>'string',
            'notes'=>'string'
        ]);

        $checkReqId = $request->id;
        $checkupWhere = CheckupNotes::where('user_id',$userId)->where('id',$checkReqId)->first();
        
        if (!$checkupWhere){
            return response()->json([
                'status'=>'error',
                'message'=>'No checkup notes available'
            ],404);
        }

        $checkId = $checkupWhere->get()->pluck('id')->toArray();

        if (!in_array($checkReqId,$checkId)){
            return response()->json([
                'status'=>'error',
                'message'=>'The inputted id does not exist'
            ],400);
        }

        if ($request->filled('status')){
            $checkupWhere->status = $request->input('status');
        }
        if ($request->filled('color_status')){
            $checkupWhere->color_status = $request->input('color_status');
        }
        if ($request->filled('notes')){
            $checkupWhere->notes = $request->input('notes');
        }

        $checkupWhere->save();
        $data = $checkupWhere->where('id',$checkReqId)->get();

        

        return response()->json([
            'status'=>'success',
            'data'=>$data
        ],200);
    }
}
