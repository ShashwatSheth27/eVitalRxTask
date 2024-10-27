<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    private function sendOTPEmail($user)
    {
        if(empty($user)) return false;
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires = now()->addMinutes(10);
        $user->save();

        $data['content'] = 'Your OTP to verify the email is: ' . $otp;
        $data['title'] = 'eVitalRx email verification';
        $data['email'] = $user->email;

        try {
            Mail::send('mail_template', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'password_confirmation' => 'required',
            'phone_no' => 'required|digits:10|unique:users',
            'gender' => 'required|string',
            'dob' => 'required|date|before_or_equal:yesterday',
            'address' => 'required|string|max:150',
        ]);

        if ($validator->fails()) return response()->json(['success' => 0, 'error' => [$validator->errors()]]);
        $validatedData = $request->all();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'phone_no' => $validatedData['phone_no'],
            'gender' => $validatedData['gender'],
            'dob' => $validatedData['dob'],
            'address' => $validatedData['address'],
        ]);

        $mailStatus = self::sendOTPEmail($user);
        if (!empty($mailStatus)) $resData['message'] = 'Email sent successfully';

        $resData['success'] = 1;
        $resData['data']['userId'] = $user->id;
        return response()->json($resData);
    }

    public function verifyEmailOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) return response()->json(['success' => 0, 'error' => [$validator->errors()]]);

        $user = User::find($request->userId);

        if ($user->otp === $request->otp && Carbon::now()->lte($user->otp_expires_at)) {
            $user->email_verified_at = Carbon::now();
            $user->otp = null;
            $user->otp_expires = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['success' => 1, 'data' => ['token' => $token], 'message' => 'Email verified successfully.']);
        }
        return response()->json(['success' => 0, 'message' => 'Invalid or expired OTP.']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) return response()->json(['success' => 0, 'error' => [$validator->errors()]]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => 0, 'error' => 'Invalid email or password.']);
        }
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['success' => '1', 'data' => ['token' => $token]]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users',
        ]);
        
        if($validator->fails()) return response()->json(['success'=>0,'error'=>[$validator->errors()]]);
        $token = Str::random(60);
        PasswordReset::updateOrCreate([
            'email' => $request->email,
        ],[
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $verificationLink = url('api/reset-password?token=' . $token);

        try {
            $mailData['email'] = $request->email;
            $mailData['title'] = 'Password Reset Link';
            $mailData['content'] = "Your password reset link is: $verificationLink. Update your password within next 30 minutes.";
            Mail::send('mail_template',['data' => $mailData], function ($message) use ($mailData) {
                $message->to($mailData['email'])->subject($mailData['title']);
            });
        } catch (\Exception $e) {
            return response()->json(['success'=>0,'error'=>$e->getMessage()]);
        }

        return response()->json(['success'=>1,'message' => 'Password reset link sent to your email.']);
    }

    public function resetPassword(Request $request)
    {
        if(empty($request->token)) return response()->json(['success'=>0]);
        $timeLimitInMinutes = 30;
        $tokenDetails = PasswordReset::where('token',$request->token)->get();
        if(!count($tokenDetails) > 0 || now()->diffInMinutes($tokenDetails[0]['created_at']) > $timeLimitInMinutes)
            return response()->json(['success'=>0,'error'=>'Invalid or expired token found in the request']);
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|confirmed|min:8',
            'password_confirmation' => 'required',
        ]);

        if($validator->fails()) return response()->json(['success'=>0,'error'=>[$validator->errors()]]);
        $user = User::where('email', $tokenDetails[0]['email'])->first();
        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();
        return response()->json(['success' => '1', 'message' => 'Password reset successfully.']);
    }
}
