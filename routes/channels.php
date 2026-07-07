<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conversation = Conversation::find($id);
    if (!$conversation) {
        return false;
    }

    return $conversation->participants()->where('user_id', $user->id)->exists();
});
