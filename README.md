<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Chat App using pusher & restapi synced for webapplications and mobileapplications


<p>NOTE: pusher only works on live server or you can use ngrok for local.
•	Create Laravel project and configure db.
•	Make pusher id and make channel on pusher get credentials from there and save it on .env file
•	PUSHER_APP_ID="1824635"
•	PUSHER_APP_KEY="eaaf59b964fbd682eab8"
•	PUSHER_APP_SECRET="0ce5d033bb4ee0011b98"
•	PUSHER_HOST=
•	PUSHER_PORT="443"
•	PUSHER_SCHEME="https"
•	PUSHER_APP_CLUSTER="ap2"
•	BROADCAST_DRIVER=pusher

•	Run this command # composer require pusher/pusher-php-server
•	Make model for messages and users
•	php artisan make:model Message -m

Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->string('message');
            $table->text('avatar');
            $table->text('attachments');
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
•	php artisan migrate







•	php artisan make:event MessageSent
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('MessageSent event dispatched: ' . $message->id); // Add this line
    }

    public function broadcastOn()
    {
        return new PrivateChannel('ChatAppForYBL.' . $this->message->receiver_id);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}

•	php artisan make:controller ChatController
class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = Message::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'avatar' => 'https://codeskulptor-demos.commondatastorage.googleapis.com/GalaxyInvaders/back05.jpg',
            'attachments' => 'https://codeskulptor-demos.commondatastorage.googleapis.com/GalaxyInvaders/back02.jpg'
        ]);
        broadcast(new MessageSent($message))->toOthers();
        return response()->json(['message' => $message]);
    }
    public function getMessages(Request $request)
    {
        $userId = $request->sender_id;
        $otherUserId = $request->receiver_id;

        $messages = Message::where(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $userId)->where('receiver_id', $otherUserId);
        })->orWhere(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $otherUserId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json(['messages' => $messages]);
    }
}
•	Make changes on broadcasting.php
'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],
        'aliases' => [
            'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        ],
        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        'log' => [
            'driver' => 'log',
        ],
        'null' => [
            'driver' => 'null',
        ],

    ],
•	
•	Goto routes/ channels and write this code
Broadcast::channel('ChatAppForYBL.{receiverId}', function ($user,
$receiverId) {
    return (int) $user->id === (int) $receiverId || true; // Update the condition as necessary
});




•	For routes/api
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
    $userData = json_encode([
        'id' => "2",
    ]);
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
</p>
