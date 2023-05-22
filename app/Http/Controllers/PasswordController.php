<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\User;

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;
use App\Constants\General;

// Libraries
use Hash;

class PasswordController extends Controller
{
    /**
     * Forgot Password
     *
     * @param  [string] email
     * @return [string] message
     * @return [object] result
     */
    public function forgot(Request $request)
    {
        $validator = validateData($request,'FORGOT_PASSWORD');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        $user = User::findUserByUsername($request->username);
        if(empty($user))
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::USER_NOT_EXISTED);

        if(is_null($user->email))
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::EMAIL_NOT_ATTACHED);

        $email = $user->email;
        $code = time();

        \Mail::send('Mails.forgot-password', [
            'code' => $code,
        ], function ($message) use ($email){
            $message->from('info@Sample.com', 'The Sample Team');
            $message->to($email)->subject('Sample | Password Reset');
        });

        return makeResponse(ResponseCode::SUCCESS, Message::FORGOT_SUCCESS, $code);
    }

    /**
     * Reset Password
     *
     * @param  [string] email
     * @param  [string] password
     * @return [string] message
     * @return [object] result
     */
    public function reset(Request $request)
    {
        $validator = validateData($request,'RESET_PASSWORD');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);
        \DB::beginTransaction();
        try
        {
            $user = User::where('username', $request->username)->where('is_deleted', General::FALSE)->first();
            if(empty($user))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::USER_NOT_EXISTED);

            $user->password = bcrypt($request->password);
            $user->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }

        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL);
    }


    /**
     * Change Password
     *
     * @param  [string] old_password
     * @param  [string] new_password
     * @return [string] message
     * @return [object] result
     */
    public function change(Request $request)
    {
        $auth_user = $request->user();

        $validator = validateData($request,'CHANGE_PASSWORD');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            if(!Hash::check($request->old_password, $auth_user->password))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::INVALID_OLD_PASSWORD);

            $user =  User::where('id', $auth_user->id)
            ->update([
                'password' => bcrypt($request->new_password),
            ]);
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $user);
    }
}
