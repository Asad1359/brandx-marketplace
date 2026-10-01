<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| USER PRIVATE CHANNEL
|--------------------------------------------------------------------------
| Every authenticated user has their own private channel.
| This is used to deliver 1-to-1 and group messages in real-time.
*/

Broadcast::channel('chat.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});


/*
|--------------------------------------------------------------------------
| ADMIN CHANNEL
|--------------------------------------------------------------------------
*/

Broadcast::channel('chat.admin', function ($user) {
    return $user->role === 'admin';
});


/*
|--------------------------------------------------------------------------
| GROUP CHANNEL
|--------------------------------------------------------------------------
| (Optional) If you want a per-group channel instead of user channels.
*/

Broadcast::channel('chat.group.{groupId}', function ($user, $groupId) {
    return \DB::table('conversation_members')
        ->where('conversation_id', $groupId)
        ->where('user_id', $user->id)
        ->exists();
});