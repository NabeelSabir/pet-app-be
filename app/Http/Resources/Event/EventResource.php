<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Sample_id' => $this->Sample_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'start_date_time' => $this->start_date_time,
            'end_date_time' => $this->end_date_time,
            'start_time_format' => $this->start_date_time->format('g:i a'),
            'end_time_format' => $this->end_date_time->format('g:i a'),
            'start_date' => $this->start_date_time->format('Y-m-d'),
            'start_time' => $this->start_date_time->format('H:i:s'),
            'end_date' => $this->end_date_time->format('Y-m-d'),
            'end_time' => $this->end_date_time->format('H:i:s'),
            'repeat' => $this->repeat,
            'repeat_end_date_time' => $this->repeat_end_date_time,
            'is_deleted' => $this->is_deleted,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'repeat_type' => $this->repeat_type,
            'Sample' => $this->Sample ?? '',

        ];
    }
}
