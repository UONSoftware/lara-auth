<?php

namespace UonSoftware\LaraAuth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    private $data;
    
    private $user = null;
    
    /**
     * Create a new event instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function getUser()
    {
        if ($this->user !== null) {
            return $this->user;
        }
        
        $model = config('lara_auth.user_model');
        if ($this->data instanceof $model) {
            $this->user = $this->data;
        } else {
            if (is_array($this->data)) {
                $this->user = $model::query()
                    ->where($this->data['field'], '=', $this->data['value'])
                    ->firstOrFail();
            }
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
