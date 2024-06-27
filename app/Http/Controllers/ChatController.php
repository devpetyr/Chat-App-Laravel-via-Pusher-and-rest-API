<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;

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

