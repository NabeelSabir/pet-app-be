<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\User;
use App\Models\Sample;
use App\Models\SampleUser;

// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;

class SampleController extends Controller
{
    /**
     * Add Sample
     *
     * @param  [string] name
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_Sample');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            $image = $request->file('image');
            $fileName = makeFileName($image);

            // Save file with the given name
            saveSampleProfileImage($image,$fileName);


	        // Create Sample
            $Sample = Sample::create([
             'name' => $request->name,
             'code' => time(),
             'image'=> $fileName
         ]);

	        // Make relation between Sample and user
            $SampleUser = SampleUser::create([
             'Sample_id' => $Sample->id,
             'user_id' => $authUser->id,
             'type' => General::Sample_OWNER
         ]);

            \DB::commit();
        }
        catch (\Exception $e)
        {
          \DB::rollBack();
          return makeResponse(ResponseCode::ERROR, $e->getMessage());
      }
      return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $Sample);
  }

    /**
     * Edit Sample
     *
     * @param  [string] type
     * @param  [string] size
     * @param  [string] breed
     * @param  [string] Weight
     * @param  [object] image
     * @return [string] message
     * @return [object] result
     */
    public function edit(Request $request, $id)
    {
        $authUser = $request->user();

        $validator = validateData($request,'EDIT_Sample');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            // Check if Sample exist
            $Sample = Sample::find($id);
            if(empty($Sample))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::INVALID_Sample);

            if($request->hasfile('image'))
            {
                $image = $request->file('image');
                $fileName = makeFileName($image);
                // Save file with the given name
                saveSampleProfileImage($image,$fileName);

                $Sample->image = $fileName;
            }
            isset($request->size) ? $size = $request->size : $size = $Sample->size;
            isset($request->breed) ? $breed = $request->breed : $breed = $Sample->breed;
            isset($request->weight) ? $weight = $request->weight : $weight = $Sample->weight;
            isset($request->length) ? $length = $request->length : $length = $Sample->length;
            isset($request->type) ? $type = $request->type : $type = $Sample->type;
            $Sample->name = $request->name;
            $Sample->size = $size;
            $Sample->type = $type;
            $Sample->weight = $weight;
            $Sample->weight_unit = $request->weight_unit;
            $Sample->length = $length;
            $Sample->length_unit = $request->length_unit;
            $Sample->breed = $breed;

            $Sample->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $Sample);
    }

    /**
     * Edit Sample Image
     *
     * @param  [object] image
     * @return [string] message
     * @return [object] result
     */
    public function editImage(Request $request, $id)
    {
        $authUser = $request->user();

        \DB::beginTransaction();
        try
        {
            // Check if Sample exist
            $Sample = Sample::find($id);
            if(empty($Sample))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::INVALID_Sample);

            if($request->hasfile('image'))
            {
                $image = $request->file('image');
                $fileName = makeFileName($image);
                // Save file with the given name
                saveSampleProfileImage($image,$fileName);

                $Sample->image = $fileName;
                $Sample->save();
                \DB::commit();
            }
            else
            {
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::FILE_REQUIRED);
            }

        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $Sample);
    }

    /**
     * Join Sample
     *
     * @param  [string] code
     * @return [string] message
     */
    public function join(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'JOIN_Sample');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
    		// Check if code is attached to any Sample
            $Sample = Sample::whereCode($request->code)->first();
            if(empty($Sample))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::Sample_INVALID_CODE);

	        // Check if Sample and user already has realetion or not
            $SampleUser = SampleUser::whereSampleId($Sample->id)->whereUserId($authUser->id)->first();
            if(!empty($SampleUser))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::Sample_ALREADY_ADDED);


	       // Make relation between Sample and user
            $SampleUser = SampleUser::create([
                'Sample_id' => $Sample->id,
                'user_id' => $authUser->id,
                'type' => General::Sample_COOWNER
            ]);

            \DB::commit();
            newUserNotification($Sample->id, $authUser->id);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());
        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL);
    }

    /**
     * Leave Sample
     *
     * @param  [string] code
     * @return [string] message
     */
    public function leave(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'LEAVE_Sample');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            // Check if Sample exist
            $Sample = Sample::find($request->Sample_id);
            if(empty($Sample))
                return makeResponse(ResponseCode::NOT_SUCCESS, Message::INVALID_Sample);

            // Make relation between Sample and user
            $SampleUser = SampleUser::where('Sample_id', $Sample->id)
            ->where('user_id', $authUser->id)
            ->delete();
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
     * List Sample
     *
     * @return [string] message
     * @return [object] result
     */
    public function list(Request $request)
    {
      $authUser = $request->user();
      $user_id = $authUser->id;

      $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');
      $Samples = Sample::whereIn('id', $SampleIds)
      ->withCount(['SampleUsers as is_owner' => function ($query) use ($user_id) {
        $query->where('type','Owner')->where('user_id', $user_id);
    }])->get();

      return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $Samples);
  }

    /**
     * Get Sample By Code
     *
     * @return [string] message
     * @return [object] result
     */
    public function find(Request $request, $code)
    {
        $authUser = $request->user();

        // Check if code is attached to any Sample
        $Sample = Sample::whereCode($code)->first();
        if(empty($Sample))
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::Sample_INVALID_CODE);

        $attchedStatus =  false;
        // Check if Sample and user already has realetion or not
        $SampleUser = SampleUser::whereSampleId($Sample->id)->whereUserId($authUser->id)->first();
        if(!empty($SampleUser))
            $attchedStatus = true;


        $result = [
            "attachedStatus" => $attchedStatus,
            "SampleDetails" => $Sample
        ];

        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

      /**
     * List Sample Users
     *
     * @return [string] message
     * @return [object] result
     */
    public function SampleUsers(Request $request)
    {
      $authUser = $request->user();
      $user_id = $authUser->id;
      $SampleId = $request->Sample_id;
      $validator = validateData($request,'Sample_USERS');
      if ($validator['status'])
          return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);
    try{
            $SampleUserIds = SampleUser::where('Sample_id',$SampleId)->pluck('user_id');
            $Samples = User::whereIn('id', $SampleUserIds)->get(['id','username']);
            $users = User::whereIn('id',$SampleUserIds)->select("id","username", \DB::raw("CONCAT('@',users.username) as username"))
            ->get();
    }
    catch (\Exception $e)
    {
        return makeResponse(ResponseCode::ERROR, $e->getMessage());
    }

      return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $users);
}
}
