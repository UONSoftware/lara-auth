<?php

namespace UonSoftware\LaraAuth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PasswordChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $data;

    private $user;

    /**
     * Create a new event instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getUser()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $model = config('lara_auth.user_model');
        if ($this->data instanceof $model) {
            $this->user = $this->data;
        } elseif (is_array($this->data)) {
            $this->user = $model::query()
                ->where($this->data['field'], '=', $this->data['value'])
                ->firstOrFail();
        }

        return $this->user;
    }
}
