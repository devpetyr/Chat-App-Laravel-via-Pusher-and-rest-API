<!DOCTYPE html>
<html>
<head>
    <title>Chat App</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

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
        .chat-container {
            height: 80vh; /* Adjust the height as needed */
            display: flex;
            flex-direction: column;
        }
        #messages {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            height: 500px;
            max-height: 500px;
        }
        .input-group {
            margin-top: auto; /* Push the input group to the bottom */
        }
        .message-info {
            font-size: 0.8em;
            color: #888;
        }
    </style>
    <script>
        function getReceiverUserId() {
            return {{$receiver_id}}; // Replace with actual logic to get the receiver user's ID
        }

        function getSenderUserId() {
            return {{$sender_id}}; // Replace with actual logic to get the sender user's ID
        }

        function createRoomId(id1, id2) {
            const sortedIds = [id1, id2].sort((a, b) => a - b);
            return `room_${sortedIds[0]}_${sortedIds[1]}`;
        }

        document.addEventListener("DOMContentLoaded", function() {
            Pusher.logToConsole = true;

            var receiverId = getReceiverUserId();
            var senderId = getSenderUserId();
            fetchPreviousMessages(senderId, receiverId);

            var roomId = createRoomId(senderId, receiverId);

            var pusher = new Pusher('eaaf59b964fbd682eab8', {
                cluster: 'ap2',
                encrypted: true,
                authEndpoint: 'https://e917-2400-adc1-185-d900-bc90-4618-8e2c-6d34.ngrok-free.app/api/pusher/auth?user_id=' + senderId, // Use sender ID here
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


            var channel = pusher.subscribe('private-ChatAppForYBL.' + roomId); // Subscribe to the room's channel

            var event = $('#event').val(); // Get event name from hidden input

            channel.bind(event, function(data) {
                console.log('New message received:', data);
                var messageContainer = document.getElementById('messages');
                var messageElement = document.createElement('div');

                // Create sender and receiver avatars based on userData
                var senderAvatar = '';
                var receiverAvatar = '';
                if (data.message.sender_id === senderId) {
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

                var messageTime = moment(data.message.created_at).format('h:mm A'); // Format the time
                var messageInfo = '<div class="message-info">' + messageTime + '</div>';
                messageElement.innerHTML += messageInfo;

                messageContainer.appendChild(messageElement);
                scrollToBottom();
            });

            document.getElementById('sendButton').addEventListener('click', function() {
                sendMessage(senderId, receiverId);
            });
        });

        function fetchPreviousMessages(senderId, receiverId) {
            $.ajax({
                url: 'https://e917-2400-adc1-185-d900-bc90-4618-8e2c-6d34.ngrok-free.app/api/get-messages',
                method: 'POST',
                data: {
                    sender_id: senderId,
                    receiver_id: receiverId,
                },
                success: function(data) {
                    displayPreviousMessages(data.messages);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching previous messages:', error);
                }
            });
        }

        function displayPreviousMessages(messages) {
            var messageContainer = document.getElementById('messages');
            messageContainer.innerHTML = ''; // Clear existing messages

            messages.forEach(function(message) {
                var messageElement = document.createElement('div');

                var senderAvatar = '';
                var receiverAvatar = '';
                if (message.sender_id === getSenderUserId()) {
                    receiverAvatar = '<img src="' + message.avatar + '" class="avatar-sent">';
                    messageElement.innerHTML = receiverAvatar + message.message;
                    messageElement.classList.add('message-sent');
                } else {
                    senderAvatar = '<img src="' + message.avatar + '" class="avatar-received">';
                    messageElement.innerHTML = senderAvatar + message.message;
                    messageElement.classList.add('message-received');
                }

                if (message.attachments) {
                    var attachmentElement = document.createElement('a');
                    attachmentElement.href = message.attachments;
                    attachmentElement.target = '_blank';
                    attachmentElement.textContent = 'Attachment';
                    attachmentElement.classList.add('attachment');
                    messageElement.appendChild(attachmentElement);
                }

                var messageTime = moment(message.created_at).format('h:mm A'); // Format the time
                var messageInfo = '<div class="message-info">' + messageTime + '</div>';
                messageElement.innerHTML += messageInfo;

                messageContainer.appendChild(messageElement);
                scrollToBottom();
            });
        }

        function sendMessage(senderId, receiverId) {
            var message = document.getElementById('messageInput').value;
            if (message.trim() === '') {
                alert('Message cannot be empty');
                return;
            }

            $.ajax({
                url: 'https://e917-2400-adc1-185-d900-bc90-4618-8e2c-6d34.ngrok-free.app/api/send-message',
                method: 'POST',
                data: {
                    sender_id: senderId,
                    receiver_id: receiverId,
                    message: message
                },
                success: function(data) {
                    document.getElementById('messageInput').value = ''; // Clear the input field
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', error);
                }
            });
        }

        function scrollToBottom() {
            var messageContainer = document.getElementById('messages');
            messageContainer.scrollTop = messageContainer.scrollHeight;
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
                        <div class="input-group mt-3">
                            <input type="text" id="messageInput" class="form-control" placeholder="Type your message here...">
                            <button id="sendButton" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
