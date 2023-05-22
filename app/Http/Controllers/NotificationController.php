<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\Notification;

// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;

class NotificationController extends Controller
{
    /**
     * Add Notification
     *
     * @param  [string] name
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_NOTIFICATION');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            // Create Notification
            $notification = Notification::create([
                'Sample_id' => $request->Sample_id,
                'user_id' => $authUser->id,
                'text' => $request->text
            ]);
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
    }

    /**
     * List Notifications
     *
     * @return [string] message
     * @return [object] result
     */
    public function list(Request $request)
    {
        $authUser = $request->user();

        $notifications = Notification::whereUserId($authUser->id)->with('post', 'comment', 'fromUser', 'user', 'Sample')->orderBy('id', 'desc')->paginate(10);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notifications);
    }

    /**
     * Notification Details
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function detail(Request $request, $id)
    {
        $authUser = $request->user();
        $notification = Notification::where('id', $id)->first();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
    }

    /**
     * Delete Notification
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function delete(Request $request, $id)
    {
        $authUser = $request->user();
        $notification = Notification::where('id', $id)->delete();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
    }

    /**
     * Read Notification
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function read(Request $request, $id)
    {
        $authUser = $request->user();
        $notification = Notification::where('id', $id)->first();
        $notification->is_read = 1;
        $notification->save();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
    }

        /**
     * Read Notification
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
        public function readAll(Request $request)
        {
            $authUser = $request->user();
            $notification = Notification::where('user_id', $authUser->id)->update([
                'is_read' => 1,
            ]);
            return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
        }
     /**
     * Count Notification Notification
     *
     * @return [string] message
     * @return [object] result
     */
     public function unreadCount(Request $request)
     {
        $authUser = $request->user();
        $notification = Notification::where('user_id', $authUser->id)->where('is_read', 0)->count();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $notification);
    }

}
