<?php

namespace App\Broadcasting;

use App\Models\Message;
use App\Models\User;

class MessageChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    
    /**
     * @param \App\User    $user
     *
     * @return bool
     */public function join(User $user, $received_id)
    {
        return $user->id === $received_id;
    }
}
