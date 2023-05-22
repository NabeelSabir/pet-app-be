<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'password'], function() {
    Route::post('forgot', 'PasswordController@forgot');
    Route::post('reset', 'PasswordController@reset');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::post('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        Route::post('profile-update', 'AuthController@profileUpdate');
    });
});


Route::group(['middleware' => 'auth:api'], function () {

    Route::group(['prefix' => 'Sample'], function() {
        Route::post('add', 'SampleController@add');
        Route::post('edit/{id}', 'SampleController@edit');
        Route::post('edit-image/{id}', 'SampleController@editImage');
        Route::post('join', 'SampleController@join');
        Route::post('leave', 'SampleController@leave');
        Route::get('list', 'SampleController@list');
        Route::get('find/{code}', 'SampleController@find');
        Route::post('users', 'SampleController@SampleUsers');
    });


    Route::group(['prefix' => 'post'], function() {
        Route::post('add', 'PostController@add');
        Route::get('list', 'PostController@list');
        Route::get('detail/{id}', 'PostController@detail');
        Route::post('update', 'PostController@update');
        Route::get('delete/{id}', 'PostController@delete');
    });

    Route::group(['prefix' => 'notification'], function() {
        Route::post('add', 'NotificationController@add');
        Route::get('list', 'NotificationController@list');
        Route::get('detail/{id}', 'NotificationController@detail');
        Route::get('delete/{id}', 'NotificationController@delete');
        Route::get('read/{id}', 'NotificationController@read');
        Route::get('unread-count', 'NotificationController@unreadCount');
        Route::get('read_all', 'NotificationController@readAll');
    });

    Route::group(['prefix' => 'event'], function() {
        Route::post('add', 'EventController@add');
        Route::post('list', 'EventController@list');
        Route::get('detail/{id}', 'EventController@detail');
        Route::get('cancel/{id}', 'EventController@cancel');
        Route::post('edit/{id}', 'EventController@edit');
        Route::post('store/', 'EventController@store');
        Route::post('update/', 'EventController@update');
        Route::post('calender/', 'EventController@calender');
        Route::get('my-events/', 'EventController@myEvents');
        Route::get('upcoming-events/','EventController@upComingEvents');
        Route::get('event-detail/','EventController@eventDetail');
    });

    Route::group(['prefix' => 'comment'], function() {
        Route::post('add', 'CommentController@add');
    });

    Route::group(['prefix' => 'device-token'], function() {
        Route::post('add', 'DeviceTokenController@add');
    });

    Route::group(['prefix' => 'like'], function() {
        Route::get('add/{post_id}', 'LikeController@add');
        Route::get('remove/{post_id}', 'LikeController@remove');
    });

    Route::group(['prefix' => 'user-notification-setting'], function() {
        Route::post('update', 'UserNotificationSettingController@update');
        Route::get('list', 'UserNotificationSettingController@list');
    });

    Route::group(['prefix' => 'password'], function() {
        Route::post('change', 'PasswordController@change');
    });
});
