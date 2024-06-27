<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Events\MessageSent;
use App\Models\Message;

Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::post('/get-messages', [ChatController::class, 'getMessages']);
Route::get('/test-pusher', function () {
    $message = Message::create([
        'sender_id' => 1,
        'receiver_id' => 2,
        'message' => 'Testing Pusher',
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return 'Event has been broadcasted!';
});

Route::post('/pusher/auth', function(Request $request) {
    $socketId = $request->input('socket_id');
    $channelName = $request->input('channel_name');
    $userId = $request->query('user_id');

    $userData = json_encode([
        'id' => $userId,
    ]);

    $key = 'eaaf59b964fbd682eab8';
    $secret = '0ce5d033bb4ee0011b98';

    $stringToSign = "{$socketId}:{$channelName}:{$userData}";
    $signature = hash_hmac('sha256', $stringToSign, $secret);

    $auth = "{$key}:{$signature}";

    return response()->json([
        'auth' => $auth,
        'channel_data' => $userData,
    ]);
});

Route::get('/{sender_id}/{receiver_id}', function ($sender_id, $receiver_id) {
    return view('welcome2', compact('sender_id', 'receiver_id'));
});
Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::post('/get-messages', [ChatController::class, 'getMessages']);
Route::get('/test-pusher', function () {
    $message = Message::create([
        'sender_id' => 1,
        'receiver_id' => 2,
        'message' => 'Testing Pusher',
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return 'Event has been broadcasted!';
});

Route::post('/pusher/auth', function(Request $request) {
    $socketId = $request->input('socket_id');
    $channelName = $request->input('channel_name'); // Ensure you receive channel_name
    $userId = $request->query('user_id');

    // Example: JSON-encoded user data (replace with your actual logic)
    $userData = json_encode([
        'id' => '2',
        'avatar' => 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.freepik.com%2Ffree-photos-vectors%2Favatar&psig=AOvVaw2Qi6kpSJdVsOCqVbrXTlNT&ust=1719497006596000&source=images&cd=vfe&opi=89978449&ved=0CBEQjRxqFwoTCLiXjNe3-YYDFQAAAAAdAAAAABAE', // Replace with actual column name storing avatar URL
        // Add other user properties as needed
    ]);

    // $userData = json_encode([
    //     'id' => $userId,
    // ]);

    // Your Pusher app credentials
    $key = 'eaaf59b964fbd682eab8';
    $secret = '0ce5d033bb4ee0011b98';

    // Create HMAC signature including userData for presence channels
    $stringToSign = "{$socketId}:{$channelName}:{$userData}";
    $signature = hash_hmac('sha256', $stringToSign, $secret);

    // Construct authentication response
    $auth = "{$key}:{$signature}";

    return response()->json([
        'auth' => $auth,
        'channel_data' => $userData, // Include if it's a presence channel
    ]);
});



