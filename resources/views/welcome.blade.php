<!DOCTYPE html>
<html>
<head>
    <title>Chat App</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <input type="hidden" id="event" value="App\Events\MessageSent">
    <style>
        .message-sent {
            /* display: flex;
            align-items: flex-end; */
            text-align: end;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 100%;
            background-color: #DCF8C6;
        }
        .message-received {
            display: flex;
            align-items: flex-start;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 100%;
            background-color: #EAEAEA;
        }
        .avatar-sent {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            display: inline;
        }
        .avatar-received {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            display: inline;
        }
        .attachment {
            display: block;
            margin-top: 5px;
            max-width: 100%;
            max-height: 200px; /* Adjust max-height as needed */
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Pusher.logToConsole = true;

            var pusher = new Pusher('eaaf59b964fbd682eab8', {
                cluster: 'ap2',
                encrypted: true,
                authEndpoint: 'https://9de8-2400-adc1-185-d900-bc83-4148-fce-1fbc.ngrok-free.app/api/pusher/auth',
                auth: {
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            pusher.connection.bind('state_change', function(states) {
                console.log('Pusher State Change:', states.current);
                if (states.current === 'disconnected') {
                    // Handle reconnection logic if needed
                    console.log('Pusher disconnected. Attempting to reconnect...');
                }
            });

            var receiverId = getCurrentUserId(); // Replace with actual logic to get current user's ID

            var channel = pusher.subscribe('private-ChatAppForYBL.' + receiverId);

            var event = $('#event').val(); // Get event name from hidden input

            channel.bind(event, function(data) {
                console.log('New message received:', data);
                var messageContainer = document.getElementById('messages');
                var messageElement = document.createElement('div');

                // Create sender and receiver avatars based on userData
                var senderAvatar = '';
                var receiverAvatar = '';
                if (data.message.sender_id === receiverId) {
                    receiverAvatar = '<img src="' + data.message.avatar + '" class="avatar-sent">';
                    messageElement.innerHTML = receiverAvatar + data.message.message;
                    messageElement.classList.add('message-sent');

                } else {
                    senderAvatar = '<img src="' + data.message.avatar + '" class="avatar-received">';
                    messageElement.innerHTML = senderAvatar + data.message.message;
                    messageElement.classList.add('message-received');
                }

                // Check if attachments are present and append them
                if (data.message.attachments) {
                    var attachmentElement = document.createElement('a');
                    attachmentElement.href = data.message.attachments;
                    attachmentElement.target = '_blank'; // Open attachment in new tab
                    attachmentElement.textContent = 'Attachment';
                    attachmentElement.classList.add('attachment');
                    messageElement.appendChild(attachmentElement);
                }

                messageContainer.appendChild(messageElement);
            });
        });

        // Example function to get current user ID dynamically
        function getCurrentUserId() {
            // Implement logic to fetch current user's ID
            return 2; // Replace with actual logic
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="row ">
                    <div class="col-md-12 text-center">
                        <h1>Chat App</h1>
                    </div>
                </div>
                <div class="row">
                    @include('sidebar')
                    <div class="col-md-6">
                        <div id="messages"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
