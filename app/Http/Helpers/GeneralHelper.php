<?php

use App\User;
use App\Models\Post;
use App\Models\Notification;
use App\Models\UserNotificationSetting;
use App\Models\DeviceToken;
use App\Models\Comment;
use App\Models\SampleUser;
use App\Models\Sample;
use App\Models\Event;



function savePostFile($file, $fileName)
{
	$file->move(storage_path('/app/public/posts'), $fileName);
}

function saveSampleProfileImage($file, $fileName)
{
	$file->move(storage_path('/app/public/SampleProfile'), $fileName);
}


function makeUniqueFileName($file, $uniqueKey)
{
	return (time() . $uniqueKey . '.' .$file->getClientOriginalExtension());
}

function makeFileName($file)
{
	return (time() . '.' .$file->getClientOriginalExtension());
}

function deleteFiles($files)
{
	\Storage::delete($files);
}

function sendPushNotification($body ,$device_tokens ,$badge = 0, $additional_info = Null)
{
	$puserId;
	$fbResult = [];
	$fcm_server_api_key = env("FCM_SERVER_API_KEY");
	$data = [
		"registration_ids" => $device_tokens,
		"priority" => "normal",
		"notification" => [
			"title" => "Sample",
			"body"  => $body,
			"sound" => "default",
			"color" => "#FF5757",
			"badge" => $badge
		],
		"data" => [
			"title" => "Sample",
			"body"  => $body,
			"additional_info"  => $additional_info,

		]
	];
	$dataString = json_encode($data);
	$headers = [
		'Authorization: key=' . $fcm_server_api_key,
		'Content-Type: application/json',
	];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
	$result = curl_exec($ch);

// 	echo $result;
}

function newPostNotification($post_id, $from_user_id){
	$post = Post::where('id', $post_id)->first();

	$SampleUsers = SampleUser::where('Sample_id', $post->Sample_id)->where('user_id', '!=', $from_user_id)->pluck('user_id');

	$Sample = Sample::where('id', $post->Sample_id)->first();

	$from_user = User::find($from_user_id);

	$body = $from_user->username." added a post for ". $Sample->name;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->like == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $post->Sample_id, $post->id, 0, $from_user->id, "New Post", $body);
		$additional_info = [
			"type" => "Post",
			"id"  => $post->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}

function likeNotification($post_id, $from_user_id){
	$post = Post::where('id', $post_id)->first();

	$userNotificationSetting = UserNotificationSetting::findOrCreate($post->user_id);
	if($userNotificationSetting->like == 0)
		return;

	$from_user = User::find($from_user_id);

	$body = $from_user->username." liked your post.";
	$device_tokens = DeviceToken::where('user_id', $post->user_id)->pluck('value')->toArray();

	$notification = notificationCreate($post->user_id, $post->Sample_id, $post->id, 0, $from_user->id, "Like", $body);
	$additional_info = [
		"type" => "Post",
		"id"  => $post->id,
		"notification_id" => $notification->id,
	];
	$badge = Notification::where('is_read', 0)->where('user_id', $post->user_id)->count();
	sendPushNotification($body, $device_tokens, $badge, $additional_info);
}

function replyNotification($post_id, $comment_id){
	$post = Post::where('id', $post_id)->first();

	$userNotificationSetting = UserNotificationSetting::findOrCreate($post->user_id);
	if($userNotificationSetting->reply == 0)
		return;

	$comment = Comment::where('id', $comment_id)->first();
	$from_user = User::find($comment->user_id);

	$body = $from_user->username." replied on your post.";
	$device_tokens = DeviceToken::where('user_id', $post->user_id)->pluck('value')->toArray();

	$notification = notificationCreate($post->user_id, $post->Sample_id, $post->id, $comment_id, $from_user->id, "Reply", $body);
	$additional_info = [
		"type" => "Post",
		"id"  => $post->id,
		"notification_id" => $notification->id,
	];
	$badge = Notification::where('is_read', 0)->where('user_id', $post->user_id)->count();
	sendPushNotification($body, $device_tokens, $badge, $additional_info);
}

function newUserNotification($Sample_id, $from_user_id){
	$Sample = Sample::where('id', $Sample_id)->first();
	$Sample_owner = SampleUser::where('Sample_id', $Sample_id)->where('type', 'Owner')->first();

	$userNotificationSetting = UserNotificationSetting::findOrCreate($Sample_owner->id);
	if($userNotificationSetting->new_user == 0)
		return;

	$from_user = User::find($from_user_id);

	$body = $from_user->username." joined your Sample ". $Sample->name;
	$device_tokens = DeviceToken::where('user_id', $Sample_owner->id)->pluck('value')->toArray();

	$notification = notificationCreate($Sample_owner->user_id, $Sample_id, 0, 0, $from_user->id, "New User", $body);
	$additional_info = [
		"type" => "User",
		"id"  => $from_user_id,
		"notification_id" => $notification->id,
	];
	$badge = Notification::where('is_read', 0)->where('user_id', $Sample_owner->user_id)->count();
	sendPushNotification($body, $device_tokens, $badge, $additional_info);
}

function newEventNotification($event_id, $from_user_id){
	$event = Event::where('id', $event_id)->first();

	$SampleUsers = SampleUser::where('Sample_id', $event->Sample_id)->where('user_id', '!=', $from_user_id)->pluck('user_id');

	$Sample = Sample::where('id', $event->Sample_id)->first();

	$from_user = User::find($from_user_id);

	$body = $from_user->username." added a New Event for ". $Sample->name;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->event == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $event->Sample_id, $event->id, 0, $from_user->id, "New Event", $body);
		$additional_info = [
			"type" => "Event",
			"id"  => $event->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}

function cancelEventNotification($event, $from_user_id){
	$SampleUsers = SampleUser::where('Sample_id', $event->Sample_id)->where('user_id', '!=', $from_user_id)->pluck('user_id');

	$Sample = Sample::where('id', $event->Sample_id)->first();

	$from_user = User::find($from_user_id);

	$body = $from_user->username." Cancel Event of ". $Sample->name;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->event == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $event->Sample_id, $event->id, 0, $from_user->id, "Cancel Event", $body);
		$additional_info = [
			"type" => "Event",
			"id"  => $event->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}

function updateEventNotification($event, $from_user_id){

	$SampleUsers = SampleUser::where('Sample_id', $event->Sample_id)->where('user_id', '!=', $from_user_id)->pluck('user_id');

	$Sample = Sample::where('id', $event->Sample_id)->first();

	$from_user = User::find($from_user_id);

	$body = $from_user->username." Update Event for ". $Sample->name;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->like == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $event->Sample_id, $event->id, 0, $from_user->id, "Update Event", $body);
		$additional_info = [
			"type" => "Event",
			"id"  => $event->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}

function inTimeEventNotification($event_id, $from_user_id, $type)
{
	$event = Event::where('id', $event_id)->first();

	$SampleUsers = SampleUser::where('Sample_id', $event->Sample_id)
// 	->where('user_id', '!=', $from_user_id)
	->pluck('user_id');

	$Sample = Sample::where('id', $event->Sample_id)->first();

	$from_user = User::find($from_user_id);

    if($type == "now")
	    $body = "Event reminder: ". $event->title;
	else
	    $body = "Event in 15 minutes: ". $event->title;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->event == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $event->Sample_id, $event->id, 0, $from_user->id, "Notification", $body);
		$additional_info = [
			"type" => "Event",
			"id"  => $event->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}

function beforeTimeEventNotification($event_id, $from_user_id)
{
	$event = Event::where('id', $event_id)->first();

	$SampleUsers = SampleUser::where('Sample_id', $event->Sample_id)->where('user_id', '!=', $from_user_id)->pluck('user_id');

	$Sample = Sample::where('id', $event->Sample_id)->first();

	$from_user = User::find($from_user_id);

	$body = $from_user->username." Notification For Event". $Sample->name;


	for($i=0; $i<count($SampleUsers); $i++){
		$userNotificationSetting = UserNotificationSetting::findOrCreate($SampleUsers[$i]);
		if($userNotificationSetting->event == 0)
			return;
		$device_tokens = DeviceToken::where('user_id', $SampleUsers[$i])->pluck('value')->toArray();
		$notification = notificationCreate($SampleUsers[$i], $event->Sample_id, $event->id, 0, $from_user->id, "Notification", $body);
		$additional_info = [
			"type" => "Event",
			"id"  => $event->id,
			"notification_id" => $notification->id,
		];
		$badge = Notification::where('is_read', 0)->where('user_id', $SampleUsers[$i])->count();
		sendPushNotification($body, $device_tokens, $badge, $additional_info);
	}

}


function notificationCreate($user_id, $Sample_id, $post_id, $comment_id, $from_user_id, $type, $text){
	return $notification = Notification::create([
		"user_id" => $user_id,
		"Sample_id" => $Sample_id,
		"post_id" => $post_id,
		"comment_id" => $comment_id,
		"from_user_id" => $from_user_id,
		"type" => $type,
		"text" => $text
	]);
}
