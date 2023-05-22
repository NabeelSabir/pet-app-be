<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\{Comment,CommentMention};

// Constants
use App\Constants\Message;
use App\Constants\ResponseCode;

class CommentController extends Controller
{
    /**
     * Add Comment to a POST
     *
     * @param  [string] text
     * @param  [integer] post_id
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_COMMENT');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {	
	        // Create Comment
            $comment = Comment::create([
                'post_id' => $request->post_id,
                'user_id' => $authUser->id,
                'text' => $request->text
            ]);
            preg_match_all("/@(\d+)@/", $request->text, $matches);
            foreach ($matches[1] as $user_id)
            {
                $comment_mention = CommentMention::create([
                    'comment_id' => $comment->id,
                    'user_id' => $user_id,
                ]);  
            }    
            \DB::commit();
            replyNotification($request->post_id, $comment->id);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());
        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $comment);
    }
}