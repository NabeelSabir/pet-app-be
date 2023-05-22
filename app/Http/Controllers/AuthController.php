<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// Models
use App\User;
use App\Models\DeviceToken;

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] username
     * @param  [string] email
     * @param  [string] password
     * @return [string] message
     * @return [object] result
     */
    public function signup(Request $request)
    {

        $validator = validateData($request,'SIGNUP');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        $user = User::findUserByUsername($request->username);
        if(!empty($user))
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::ALREADY_EXIST);

        $user = User::createUser($request->username, $request->email, $request->password);

        return attemptLogin($request);
    }

    /**
     * Login user and create token
     *
     * @param  [string] username
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] message
     * @return [object] result
     */
    public function login(Request $request)
    {
        $validator = validateData($request,'LOGIN');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        return attemptLogin($request);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $authUser = $request->user();
        $request->user()->token()->revoke();
        $device_token = DeviceToken::where('user_id', $authUser->id)->where('value', $request->token_value)->delete();
        
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL);
    }

    /**
     * Get the authenticated User
     *
     * @return [string] message
     * @return [object] result
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $result = [
                "info" => $user,
                "tokens" => DeviceToken::where('user_id', $user->id)->get()
            ];
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

    /**
     * Update authenticated User
     *
     * @param  [string] email
     * @return [string] message
     * @return [object] result
     */
    public function profileUpdate(Request $request)
    {
        $validator = validateData($request,'UPDATE_PROFILE');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        $user = $request->user()->update(["email" => $request->email]);

        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL);
    }
}