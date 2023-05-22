<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\UserNotificationSetting;

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;

class UserNotificationSettingController extends Controller
{
    /**
     * Update Notification Setting
     *
     * @param  [tinyInteger] like
     * @param  [tinyInteger] reply
     * @param  [tinyInteger] metion
     * @param  [tinyInteger] event
     * @param  [tinyInteger] new_user
     * @return [object] result
     */
    public function update(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'UPDATE_NOTIFICATION_SETTING');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {   
            $setting = UserNotificationSetting::where('user_id', $authUser->id)
            ->update([
                "like" => $request->like,
                "reply" => $request->reply,
                "mention" => $request->mention,
                "event" => $request->event,
                "new_user" => $request->new_user,
            ]);
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
     * List Notification Setting
     *
     * @return [object] result
     */
    public function list(Request $request)
    {
        $authUser = $request->user();

        try
        {   
            $setting = UserNotificationSetting::findOrCreate($authUser->id);
        }
        catch (\Exception $e)
        {
            return makeResponse(ResponseCode::ERROR, $e->getMessage());
        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $setting);
    }
}
