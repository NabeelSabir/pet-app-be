<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\Like;

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;

class LikeController extends Controller
{
    /**
     * Add Like to a POST
     *
     * @param  [integer] post_id
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request, $post_id)
    {
        $authUser = $request->user();

        \DB::beginTransaction();
        try
        {	
	        // Create Like
            $like = Like::where('post_id', $post_id)->where('user_id', $authUser->id)->first();

            if(empty($like))
            {
                $like = Like::create([
                    'post_id' => $post_id,
                    'user_id' => $authUser->id,
                ]);
            }

            \DB::commit();
            likeNotification($post_id, $authUser->id);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $like);
    }

    /**
     * Remove Like to a POST
     *
     * @param  [integer] post_id
     * @return [string] message
     * @return [object] result
     */
    public function remove(Request $request, $post_id)
    {
        $authUser = $request->user();

        \DB::beginTransaction();
        try
        {	
	        // Remove a Like
            $like = Like::where('post_id', $post_id)->where('user_id', $authUser->id)->delete();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());
        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $like);
    }
}
