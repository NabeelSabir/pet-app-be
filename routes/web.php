<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Models\Event;
Route::get('event', 'CronController@event');

Route::get('/', function(){
	// return 'hahaha';

	sendPushNotification("test", ["cppPN1yfRVagRJHTdhuBnK:APA91bHUAO6a4PMi_xZRKbMopHLKcszoztMEkq5pC_iTWRX1CgeojgEuVfpbDVbqU8WrG0NOftAr5XcbcVX4soxJ8m288uwRduF-1g9lZk4AL9KMTueTcRJStiWrjOLigGMHE-zX64p8"]);
});
Route::get('/eventcronjob', function(){
	Storage::disk('local')->put('example.txt', 'Contents');

	return Event::notificationAlert();
	Storage::disk('local')->put('example.txt', 'wqwqwqw');

	echo "Notification Done";
});