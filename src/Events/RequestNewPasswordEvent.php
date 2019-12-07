<?php

namespace UonSoftware\LaraAuth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class RequestNewPasswordEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private $email;

    /**
     * Create a new event instance.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getUser()
    {
        if ($this->user === null) {
            $userModel = config('lara_auth.user_model');
            $this->user = $userModel::query()
                ->where('email', '=', $this->email)
                ->firstOrFail();
        }

        return $this->user;
    }


}
