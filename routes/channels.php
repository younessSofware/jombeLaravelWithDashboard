<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('messages.{receiver_id}', function ($user, $receiver_id) {
    return $user->id == $receiver_id;
});

Broadcast::channel('notifications.{user_id}', function($user, $user_id){
    return $user->id == $user_id;
});
