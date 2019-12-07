<?php

namespace UonSoftware\LaraAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use UonSoftware\LaraAuth\Events\PasswordChangedEvent;
use UonSoftware\LaraAuth\Notifications\PasswordChangedNotification;

class PasswordChangedListener implements ShouldQueue
{
    public $delay = 10;

    public $queue = 'listeners';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \UonSoftware\LaraAuth\Events\PasswordChangedEvent $event
     *
     * @return void
     */
    public function handle(PasswordChangedEvent $event): void
    {
        $notification = (new PasswordChangedNotification())
            ->onQueue('password_reset')
            ->delay(now()->addSeconds(20));
        $event->getUser()->notify($notification);
    }
}
