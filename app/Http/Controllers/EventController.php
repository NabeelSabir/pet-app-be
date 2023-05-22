<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Resource
use App\Http\Resources\Event\{EventResource,PaginatedEventResource};

// Models
use App\Models\Event;
use App\Models\SampleUser;

// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;
//
use App\Http\Requests\StoreEventRequest;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
class EventController extends Controller
{
    /**
     * Add Event
     *
     * @param  [integer] Sample_id
     * @param  [string] title
     * @param  [string] start_date_time
     * @param  [string] end_date_time
     * @param  [string] repeat_end_date_time
     * @param  [string] repeat
     * @return [string] message
     * @return [object] result
     */
    public function add(Request $request)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_EVENT');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));
            $end_date_time = date('Y-m-d H:i:s', strtotime("$request->end_date $request->end_time"));
            // Create Event
            $event = Event::create([
                'Sample_id' => $request->Sample_id,
                'user_id' => $authUser->id,
                'title' => $request->title,
                'start_date_time' => $start_date_time,
                'end_date_time' => $end_date_time,
                'repeat' => $request->repeat,
                'repeat_type' => $request->repeat_type,
                'repeat_end_date_time' => $request->repeat_end_date_time,
            ]);
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $event);
    }

    /**
     * Edit Event
     *
     * @param  [integer] id
     * @param  [integer] Sample_id
     * @param  [string] title
     * @param  [string] start_date_time
     * @param  [string] end_date_time
     * @param  [string] repeat_end_date_time
     * @param  [string] repeat
     * @return [string] message
     * @return [object] result
     */
    public function edit(Request $request, $id)
    {
        $authUser = $request->user();

        $validator = validateData($request,'ADD_EVENT');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            $event = Event::where('id', $id)->first();
            if(!$event)

            $start_date_time = carbonDate($event->start_date_time);

            if($start_date_time->isPast())
            {
                $event->update([
                    "is_deleted" => 1,
                    "deleted_at" => currentCarbonDate(General::DATE_FORMAT_2)
                ]);

                if(!$event_delete)
                    return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED);

                $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));
                $end_date_time = date('Y-m-d H:i:s', strtotime("$request->end_date $request->end_time"));
                // Create Event
                $event = Event::create([
                    'Sample_id' => $request->Sample_id,
                    'user_id' => $authUser->id,
                    'title' => $request->title,
                    'start_date_time' => $start_date_time,
                    'end_date_time' => $end_date_time,
                    'repeat' => $request->repeat,
                    'repeat_type' => $request->repeat_type,
                    'repeat_end_date_time' => $request->repeat_end_date_time,
                ]);
            }
            else
            {
                // Update Event
                $event = Event::where('id', $id)->update([
                    'Sample_id' => $request->Sample_id,
                    'user_id' => $authUser->id,
                    'title' => $request->title,
                    'start_date_time' => $start_date_time,
                    'end_date_time' => $end_date_time,
                    'repeat' => $request->repeat,
                    'repeat_type' => $request->repeat_type,
                    'repeat_end_date_time' => $request->repeat_end_date_time,
                ]);
            }

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $event);
    }

    /**
     * List Events
     *
     * @return [string] message
     * @return [object] result
     */
    public function list(Request $request)
    {
        $authUser = $request->user();
        $start_date = $request->date;
        // $start_date_time = date('Y-m-d', strtotime("$request->start_date $request->start_time"));
        $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');
        $events = Event::whereIn('Sample_id', $SampleIds)->whereDate('start_date_time','<=',$start_date)->whereDate('end_date_time','>=',$start_date)->with('Sample')->get();
        if(count($events) == 0)
            return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
        $result = EventResource::collection($events);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }


    /**
     * List Events
     *
     * @return [string] message
     * @return [object] result
     */
    public function calender(Request $request)
    {
        $authUser = $request->user();
        $results = [];
        $start_date_time = $request->start_date;
        $end_date_time = $request->end_date;
        $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');
        $result = Event::whereIn('Sample_id', $SampleIds)->whereDate('start_date_time','>=',$start_date_time)->whereDate('end_date_time','<=',$end_date_time)->get(['id','start_date_time','end_date_time']);
        if(count($result) == 0)
            return makeResponse(ResponseCode::SUCCESS, Message::RECORD_NOT_FOUND);
        $event_data = collect();
        foreach($result as $r)
        {
            $data = CarbonPeriod::create($r->start_date_time, $r->end_date_time);
            foreach($data as $d)
            {
            array_push($results,$d);
            }
            $event_data = collect($results)->unique();
        }
        $calender = $event_data->map(function ($date) {
            return $date->format('Y-m-d');
         });
        $cal = $calender->values()->all();
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $cal);
    }

    /**
     * Event Details
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function detail(Request $request, $id)
    {
        $authUser = $request->user();
        $event = Event::where('id', $id)->with('Sample')->first();
        if(!$event)
            return makeResponse(ResponseCode::SUCCESS, Message::RECORD_NOT_FOUND);
        $result = new EventResource($event);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

    /**
     * Cancel Event
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function cancel(Request $request, $id , Event $event)
    {
        $authUser = $request->user();
        \DB::beginTransaction();
        try
        {
            $event = Event::Cancel($id);
            if(!$event)
                return makeResponse(ResponseCode::SUCCESS, Message::RECORD_NOT_FOUND);
            cancelEventNotification($event, $authUser->id);
            \DB::commit();

        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());
        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $event);
    }
     /**
     * Store Event
     *
     * @param  [integer] Sample_id
     * @param  [string] title
     * @param  [string] start_date
     * @param  [string] start_time
     * @param  [string] end_date
     * @param  [string] start_time
     * @param  [string] repeat_end_date_time
     * @param  [string] repeat
     * @return [string] message
     * @return [object] result
     */
    public function store(Request $request)
    {
        $authUser = $request->user();
        $validator = validateData($request,'EVENT_STORE');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));
            $end_date_time = date('Y-m-d H:i:s', strtotime("$request->end_date $request->end_time"));
            $Sample_id = $request->Sample_id;
            $user_id = $authUser->id;
            $title = $request->title;
            $repeat = isset($request->repeat) ? json_decode($request->repeat) : NULL;
            $repeat_type = $request->repeat_type;
            $event =  Event::create($Sample_id, $user_id,$title, $start_date_time, $end_date_time,$repeat, $repeat_type);
            \DB::commit();
            newEventNotification($event->id, $authUser->id);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $event);
    }
      /**
     * update Event
     *
     * @param  [integer] Sample_id
     * @param  [string] title
     * @param  [string] start_date
     * @param  [string] start_time
     * @param  [string] end_date
     * @param  [string] start_time
     * @param  [string] repeat_end_date_time
     * @param  [string] repeat
     * @return [string] message
     * @return [object] result
     */
    public function update(Request $request , Event $event)
    {
        $authUser = $request->user();

        $validator = validateData($request,'EVENT_UPDATE');
        if ($validator['status'])
            return makeResponse(ResponseCode::NOT_SUCCESS, Message::VALIDATION_FAILED, null, $validator['errors']);

        \DB::beginTransaction();
        try
        {
            $id = $request->id;
            $event = Event::find($id);
            $update_event = $event ;
            if(!$event)
                return makeResponse(ResponseCode::SUCCESS, Message::RECORD_NOT_FOUND);

            $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));
            $end_date_time = date('Y-m-d H:i:s', strtotime("$request->end_date $request->end_time"));
            $Sample_id = $request->Sample_id;
            $user_id = $authUser->id;
            $title = $request->title;
            $repeat = isset($request->repeat) ? json_decode($request->repeat) : NULL;
            $repeat_type = $request->repeat_type;
            // cancel Previous Event
            Event::Cancel($id);
            // Store New Events
            $events =  Event::create($Sample_id, $user_id,$title, $start_date_time, $end_date_time,$repeat, $repeat_type);
            updateEventNotification($update_event, $authUser->id);
            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return makeResponse(ResponseCode::ERROR, $e->getMessage());

        }
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $events);
    }
    /**
     * my Events
     *
     * @return [string] message
     * @return [object] result
     */
    public function myEvents(Request $request)
    {
        $authUser = $request->user();
        $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));
        $events = Event::where('user_id',$authUser->id)->where('start_date_time','>',$start_date_time)->orderBy('start_date_time','asc')->get();
        $events =  $events->unique('unique_number')->values()->all();
        // Get current page form url e.x. &page=1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Create a new Laravel collection from the array data
        $eventCollection = collect($events);

        // Define how many events we want to be visible in each page
        $perPage = 10;

        // Slice the collection to get the events to display in current page
        $currentPageevents = $eventCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create our paginator and pass it to the resource
        $paginatedevents= new LengthAwarePaginator($currentPageevents , count($eventCollection), $perPage);

        // set url path for generted links
        $paginatedevents->setPath($request->url());
        $result = new PaginatedEventResource($paginatedevents);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

      /**
     * Upcoming Events
     *
     * @return [string] message
     * @return [object] result
     */
    public function upComingEvents(Request $request)
    {
        $authUser = $request->user();
        $start_date_time = date('Y-m-d H:i:s', strtotime("$request->start_date $request->start_time"));

        $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');

        $events = Event::whereIn('Sample_id', $SampleIds)->where('start_date_time','>',$start_date_time)->orderBy('start_date_time','asc')->get();
        $events =  $events->unique('unique_number')->values()->all();
        // Get current page form url e.x. &page=1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Create a new Laravel collection from the array data
        $eventCollection = collect($events);

        // Define how many events we want to be visible in each page
        $perPage = 10;

        // Slice the collection to get the events to display in current page
        $currentPageevents = $eventCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create our paginator and pass it to the resource
        $paginatedevents= new LengthAwarePaginator($currentPageevents , count($eventCollection), $perPage);

        // set url path for generted links
        $paginatedevents->setPath($request->url());
        $result = new PaginatedEventResource($paginatedevents);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

    /**
     * Event Details with date
     *
     * @param [integer] id
     * @return [string] message
     * @return [object] result
     */
    public function eventDetail(Request $request)
    {
        $authUser = $request->user();
        $start_date = $request->start_date;
        $SampleIds = SampleUser::whereUserId($authUser->id)->pluck('Sample_id');
        $events = Event::whereIn('Sample_id', $SampleIds)->whereDate('start_date_time','<=',$start_date)->whereDate('end_date_time','>=',$start_date)->with('Sample')->get();
        if(count($events) == 0 )
            return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
        $result = EventResource::collection($events);
        return makeResponse(ResponseCode::SUCCESS, Message::REQUEST_SUCCESSFUL, $result);
    }

}
