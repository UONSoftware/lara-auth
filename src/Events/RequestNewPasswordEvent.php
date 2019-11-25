<?php

namespace UonSoftware\LaraAuth\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestNewPasswordEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var null|User
     */
    private $user = null;
    private $email;

    /**
     * Create a new event instance.
     *
     * @param  string  $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return \App\User
     */
    public function getUser(): User
    {
        if ($this->user === null) {
            $this->user = User::query()
                ->where('email', '=', $this->email)
                ->firstOrFail();
        }

        return $this->user;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
