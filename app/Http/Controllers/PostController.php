<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\Post;
use App\Models\PostFiles;
use App\Models\SampleUser;
use App\Models\Comment;
use App\Models\Like;
use App\Models\PostMention;

// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;

class PostController extends Controller
{
    /**
     * Add Post
     *
     * @param  [string] name
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_POST');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            // Create Post
            $post = Post::create([
                'Sample_id' => $request->Sample_id,
                'user_id' => $authUser->id,
                'text' => $request->text
            ]);
            preg_match_all("/@(\d+)@/", $request->text, $matches);
            foreach ($matches[1] as $user_id)
            {
                $post_mention = PostMention::create([
                    'post_id' => $post->id,
                    'user_id' => $user_id,
                ]);
            }
            if($request->has('attachments'))
            {
                $files = $request->file('attachments');

                $postFileArr = [];
                $fileNameArr = [];

                foreach ($files as $key => $file)
                {
                    $fileName = makeUniqueFileName($file, $key);

                    // Save file with the given name
                    savePostFile($file,$fileName);

                    $postFileArr[] = [ "post_id" => $post->id, "file_path" => $fileName];
                    $fileNameArr[] = General::POST_FILE_DIR.$fileName;
                }
                // Create Post Files
                $postFile = PostFiles::insert($postFileArr);
            }
            \DB::commit();
            newPostNotification($post->id, $authUser->id);
            $user_id = $authUser->id;
            $comments = Comment::where('post_id', $post->id)->with('user')->get();

            $likes = Like::where('post_id', $post->id)->with('user')->get();

            $post = Post::where('id', $post->id)
            ->with('attachments')
            ->with('Sample')
            ->with('user')
            ->withCount(['likes as is_liked_by_me' => function ($query) use ($user_id) {
              $query->where('user_id', $user_id);
          }])
            ->withCount('comments')
            ->withCount('likes')
            ->first();
            $result = [
                "comments" => $comments,
                "likes" => $likes,
                "post" => $post,
            ];

        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            deleteFiles($fileNameArr);
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

    /**
     * List Posts
     *
     * @return [string] message
     * @return [object] result
     */
    public function list(Request $request)
    {
      $authUser = $request->user();
      $user_id = $authUser->id;

      $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');
      $posts = Post::whereIn('Sample_id', $SampleIds)
      ->with('attachments')
      ->with('Sample')
      ->with('user')
      ->withCount(['likes as is_liked_by_me' => function ($query) use ($user_id) {
        $query->where('user_id', $user_id);
        }])
        ->withCount(['comments as is_comment_by_me' => function ($query) use ($user_id) {
        $query->where('user_id', $user_id);
        }])
      ->withCount('comments')
      ->withCount('likes')
      ->orderBy('id','desc')->paginate(10);

      return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $posts);
  }

    /**
     * Post Details
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function detail(Request $request, $id)
    {
        $authUser = $request->user();
        $user_id = $authUser->id;

        $comments = Comment::where('post_id', $id)->with('user')->get();

        $likes = Like::where('post_id', $id)->with('user')->get();

        $post = Post::where('id', $id)
        ->with('attachments')
        ->with('Sample')
        ->with('user')
        ->withCount(['likes as is_liked_by_me' => function ($query) use ($user_id) {
          $query->where('user_id', $user_id);
      }])
        ->withCount('comments')
        ->withCount('likes')
        ->first();
        $result = [
            "comments" => $comments,
            "likes" => $likes,
            "post" => $post,
        ];

        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

        /**
     * Delete Post
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function delete(Request $request, $id)
    {
        $authUser = $request->user();
        $post = Post::where('id', $id)->with('attachments')->first();
        foreach($post['attachments'] as $post_files)
        {
            $filePath   =   storage_path('app/public/posts/'.$post_files->file_path);
            \File::delete($filePath);
            PostFiles::where('post_id', $id)->delete();
        }
        $post->delete();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL);
    }

     /**
     * Update Post
     *
     * @param  [string] name
     * @return [string] message
     * @return [object] result
     */
    public function update(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'UPDATE_POST');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            // Create Post
            $post = Post::where('id', $request->id)->update([
                'Sample_id' => $request->Sample_id,
                'user_id' => $authUser->id,
                'text' => $request->text
            ]);
            PostMention::where('post_id',$request->id)->delete();
            preg_match_all("/@(\d+)@/", $request->text, $matches);
            foreach ($matches[1] as $user_id)
            {
                $post_mention = PostMention::create([
                    'post_id' => $request->id,
                    'user_id' => $user_id,
                ]);
            }
            if($request->has('attachments'))
            {
                $files = $request->file('attachments');

                $postFileArr = [];
                $fileNameArr = [];

                foreach ($files as $key => $file)
                {
                    $fileName = makeUniqueFileName($file, $key);

                    // Save file with the given name
                    savePostFile($file,$fileName);

                    $postFileArr[] = [ "post_id" => $request->id, "file_path" => $fileName];
                    $fileNameArr[] = General::POST_FILE_DIR.$fileName;
                }
                // Create Post Files
                $postFile = PostFiles::insert($postFileArr);

                $delete_files_ids  = json_decode($request->delete_files_ids);
                foreach($delete_files_ids as $post_files)
                {
                    $post_file = PostFiles::where('id',$post_files)->first();
                   if($post_file)
                   {
                     $filePath   =   storage_path('app/public/posts/'.$post_file->file_path);
                    \File::delete($filePath);
                    $post_file->delete();
                   }
                }
            }
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            deleteFiles($fileNameArr);
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $post);
    }


}
