<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'message' => 'required|string|max:500',
        ]);

        broadcast(new MessageSent(
            $validated['username'],
            $validated['message']
        ));

        return back();
    }
}
