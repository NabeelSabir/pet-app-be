<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Constants
use App\Constants\Message;
use App\Constants\General;
use App\Constants\ResponseCode;
// Models
use App\Models\Notification;
use App\Models\SampleUser;
use App\User;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Event extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'events';
    protected $casts = [
        'start_date_time' => 'datetime:Y-m-d H:i:s','end_date_time' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'Sample_id', 'user_id','unique_number','title', 'start_date_time', 'end_date_time', 'repeat', 'repeat_type', 'repeat_end_date_time', 'is_deleted', 'deleted_at'
    ];


    public function Sample()
    {
        return $this->hasOne('App\Models\Sample','id','Sample_id');
    }

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'event_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
    public function saveQuietly()
    {
    return static::withoutEvents(function () {
        return $this->save();
    });
    }

    public static function notificationAlert()
    {
        $current_date  = Carbon::now()->subHours(5);//->format(General::DATE_FORMAT_2);
        $current_date2  = Carbon::now()->subHours(5)->format(General::DATE_FORMAT_2);
        // dd($current_date);
        $before_fifteen_minute = Carbon::now()->subHours(5)->addMinutes(16)->format(General::DATE_FORMAT_2);
        $before_fifteen_minutes = Carbon::now()->subHours(5)->addMinutes(15)->format(General::TIME_FORMAT_3);

        // return $current_date2."      ".$before_fifteen_minute;
        $events = Event::where('start_date_time','>=', $current_date2)->where('start_date_time' ,'<',$before_fifteen_minute)->get();
        // where('id',32277)->
        // return $events;
        if($events)
        {
            foreach($events as $index => $event)
            {
                // return $current_date->format(General::TIME_FORMAT_3) ." ". carbonDate($event->start_date_time, General::TIME_FORMAT_3);
                if($current_date->format(General::TIME_FORMAT_3) == carbonDate($event->start_date_time, General::TIME_FORMAT_3))
                {
                 inTimeEventNotification($event->id, $event->user_id, "now");
                }

                // return $before_fifteen_minutes."  ".carbonDate($event->start_date_time, General::TIME_FORMAT_3);
                if($before_fifteen_minutes == carbonDate($event->start_date_time, General::TIME_FORMAT_3))
                {
                    inTimeEventNotification($event->id, $event->user_id, "before15minute");
                    // beforeTimeEventNotification($event->id, $event->user_id);
                }
            }
        }
    }
    public static function create($Sample_id, $user_id,$title, $start_date_time, $end_date_time,$repeat = NULL, $repeat_type = NULL)
    {
        $unique_number = random_int(100000, 999999).strtotime(Carbon::now());
            $event  = new self;
            $event->Sample_id   =$Sample_id;
            $event->user_id  = $user_id;
            $event->title     = $title;
            $event->start_date_time    = $start_date_time;
            $event->end_date_time      = $end_date_time;
            $event->repeat  = json_encode($repeat) ?? NULL;
            $event->repeat_type  = isset($repeat_type) ? $repeat_type : 'None';
            $event->event_id  = NULL;
            $event->unique_number =$unique_number;
            $event->save();
        if(!$event->event()->exists())
        {
            $recurrences = [
                'daily'     => [
                    'times'     => 365,
                    'function'  => 'addDay'
                ],
                'Weekly'    => [
                    'times'     => 365,
                    'function'  => 'addDay'
                ],
                'Monthly'    => [
                    'times'     => 12,
                    'function'  => 'addMonth'
                ]
            ];
            $startTime = $event->start_date_time;
            $endTime = $event->end_date_time;
            $recurrence = $recurrences[$event->repeat_type] ?? null;
            if($recurrence)
                for($i = 0; $i < $recurrence['times']; $i++)
                {
                    $startTime->{$recurrence['function']}();
                    $endTime->{$recurrence['function']}();
                    $repeat_title = collect($repeat);
                    $repeat_title = $repeat_title->where('status',1)->pluck('title');
                    if($repeat_title){
                    if($event->repeat_type == "Weekly" && in_array($startTime->format('l') ,$repeat_title->toArray())){
                        $event->events()->create([
                            'user_id'  => $event->user_id,
                            'title'          => $event->title,
                            'start_date_time'    => $startTime,
                            'end_date_time'      => $endTime,
                            'repeat_type'    => $event->repeat_type,
                            'Sample_id'   =>$event->Sample_id,
                            'event_id'  => $event->id,
                            'repeat'  => json_encode($repeat) ?? NULL,
                            'unique_number' =>$unique_number,
                        ]);
                    }}
                    if($event->repeat_type == "Monthly")
                    {
                        $event->events()->create([
                            'user_id'  => $event->user_id,
                            'title'          => $event->title,
                            'start_date_time'    => $startTime,
                            'end_date_time'      => $endTime,
                            'repeat_type'    => $event->repeat_type,
                            'Sample_id'   =>$event->Sample_id,
                            'event_id'  => $event->id,
                            'repeat'  => json_encode($repeat) ?? NULL,
                            'unique_number' => $unique_number,
                        ]);
                    }
                }
        }
        return $event;
    }
    public static function Cancel($id)
    {
        $event = self::where('id', $id)->first();
        $deleted_event = $event ;
        if(!$event)
            return $deleted_event;
        if($event->events()->exists())
        {
            $events = $event->events()->pluck('id');
            $parent_id = $id;
            Event::whereIn('id', $events)->delete();
            Event::where('id', $parent_id)->delete();
        }
        else if($event->event)
        {
            $events = $event->where('event_id',$event->event->id)->whereDate('start_date_time', '>', $event->start_date_time)->pluck('id');
            $parent_id = $id;
            Event::whereIn('id', $events)->delete();
            Event::where('id', $parent_id)->delete();
        }
        Event::where('id', $id)->delete();
        return $deleted_event;
    }
}
