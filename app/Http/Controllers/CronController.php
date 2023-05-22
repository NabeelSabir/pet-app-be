<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\Event;
use App\Models\Notification;
use App\Models\SampleUser;
use App\User;

// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;

class CronController extends Controller
{
    public function event(){

        $current_date  = currentCarbonDate(General::DATE_FORMAT_2);
        $obj_arr = [];
        // Non Recurring Events
        $events = Event::where('start_date_time', '>=', $current_date)
                        ->where('repeat',null)
                        ->where('is_deleted',0)
                        ->get();

        foreach ($events as $key => $data)
        {
            if(currentCarbonDate(General::DATE_FORMAT_3) == carbonDate($data->start_date_time, General::DATE_FORMAT_3))
            {
                $user_ids = SampleUser::where('Sample_id', $data->Sample_id)->pluck('user_id');

                for ($i=0; $i < count($user_ids) ; $i++) {
                    $obj_arr[] = [
                        "user_id" => $user_ids[$i],
                        "Sample_id" => $data->Sample_id,
                        "text" => $data->title,
                    ];
                }
            }
        }


        // Recurring Events
        $events = Event::where('repeat', '!=', null)
                        ->where('is_deleted',0)
                        ->get();

        $current_date_obj = currentCarbonDate();

        foreach ($events as $key => $data)
        {
            $repeat_arr = json_decode($data->repeat);

            if(in_array($current_date_obj->dayOfWeek, $repeat_arr))
            {
                if(is_null($data->repeat_end_date_time))
                {
                    if(currentCarbonDate(General::TIME_FORMAT_3) == carbonDate($data->start_date_time, General::TIME_FORMAT_3))
                    {
                        $user_ids = SampleUser::where('Sample_id', $data->Sample_id)->pluck('user_id');

                        for ($i=0; $i < count($user_ids) ; $i++) {
                            $obj_arr[] = [
                                "user_id" => $user_ids[$i],
                                "Sample_id" => $data->Sample_id,
                                "text" => $data->title,
                            ];
                        }
                    }
                }
                else
                {
                    if(!(carbonDate($data->repeat_end_date_time))->isPast())
                    {
                        if(currentCarbonDate(General::TIME_FORMAT_3) == carbonDate($data->start_date_time, General::TIME_FORMAT_3))
                        {
                            $user_ids = SampleUser::where('Sample_id', $data->Sample_id)->pluck('user_id');

                            for ($i=0; $i < count($user_ids) ; $i++) {
                                $obj_arr[] = [
                                    "user_id" => $user_ids[$i],
                                    "Sample_id" => $data->Sample_id,
                                    "text" => $data->title,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $notification = Notification::insert($obj_arr);


    }
}
