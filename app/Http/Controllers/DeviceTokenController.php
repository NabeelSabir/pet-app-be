<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\DeviceToken;

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;

class DeviceTokenController extends Controller
{
    /**
     * Add Device Token
     *
     * @param  [string] value
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_DEVICE_TOKEN');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {   
            $device_token = DeviceToken::where('value', $request->value)->where('user_id', $authUser->id)->first();

            if(empty($device_token))
            {
                // Create Device Token
                $device_token = DeviceToken::create([
                    'value' => $request->value,
                    'user_id' => $authUser->id
                ]);
            }
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $device_token);
    }
}
