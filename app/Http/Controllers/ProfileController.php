<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'email' => 'sometimes|string|email|max:50|unique:users',
            'name' => 'sometimes|required|string|max:50',
            'phone_no' => 'sometimes|required|digits:10|unique:users',
            'gender' => 'sometimes|required|string',
            'dob' => 'sometimes|required|date|before_or_equal:yesterday',
            'address' => 'sometimes|required|string|max:150',
        ]);
        if($validator->fails()) return response()->json(['success'=>0,'error'=>[$validator->errors()]]);

        User::where('id', $request->userId)->update($request->only(['email', 'name', 'phone_no', 'gender', 'dob', 'address']));

        return response()->json(['success' => '1', 'message' => 'Profile updated successfully.']);
    }
}
