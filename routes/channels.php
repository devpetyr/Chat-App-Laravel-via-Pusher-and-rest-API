<?php

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

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('ChatAppForYBL.room_{id1}_{id2}', function ($user, $id1, $id2) {
    $sortedIds = [(int) $id1, (int) $id2];
    sort($sortedIds);
    return in_array($user->id, $sortedIds);
});

