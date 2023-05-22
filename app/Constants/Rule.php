<?php

namespace App\Constants;

class Rule
{
    // Rules According to API's
    private static $rules = [
        'LOGIN' => [
            'username' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ],
        'SIGNUP' => [
            'username' => 'required|string',
            'password' => 'required|string'
        ],
        'ADD_Sample' => [
            'name' => 'required|string',
        ],
        'EDIT_Sample' => [
            'name' => 'required|string',
            'weight_unit' => 'required|string',
            'length_unit' => 'required|string',
        ],
        'LEAVE_Sample' => [
            'Sample_id' => 'required',
        ],
        'JOIN_Sample' => [
            'code' => 'required|string',
        ],
        'ADD_POST' => [
            'Sample_id' => 'required',
            // 'attachments' => 'required',
            'text' => 'required',
        ],
        'UPDATE_POST' => [
            'Sample_id' => 'required',
            'text' => 'required',
            'id' => 'required',
        ],
        'ADD_COMMENT' => [
            'post_id' => 'required',
            'text' => 'required',
        ],
        'ADD_DEVICE_TOKEN' => [
            'value' => 'required',
        ],
        'UPDATE_NOTIFICATION_SETTING' => [
            'like' => 'required',
            'reply' => 'required',
            'mention' => 'required',
            'event' => 'required',
            'new_user' => 'required',
        ],
        'CHANGE_PASSWORD' => [
            'old_password' => 'required',
            'new_password' => 'required',
        ],
        'FORGOT_PASSWORD' => [
            'username' => 'required',
        ],
        'RESET_PASSWORD' => [
            'username' => 'required',
            'password' => 'required',
        ],
        'UPDATE_PROFILE' => [
            'email' => 'required',
        ],
        'ADD_NOTIFICATION' => [
            'Sample_id' => 'required',
            'text' => 'required',
        ],
        'EVENT_STORE' => [
            'Sample_id' => 'required',
            'title' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
        ],
        'EVENT_UPDATE' => [
            'Sample_id' => 'required',
            'title' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'id' => 'required',
        ],
        'Sample_USERS' => [
            'Sample_id' => 'required',
        ]
    ];

    public static function get($api){
      return self::$rules[$api];
  }
}
