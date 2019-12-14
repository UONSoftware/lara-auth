<?php

namespace UonSoftware\LaraAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use UonSoftware\LaraAuth\Events\PasswordChangedEvent;

class PasswordChangedListener implements ShouldQueue
{
    public $delay = 10;

    /**
     * Handle the event.
     *
     * @param \UonSoftware\LaraAuth\Events\PasswordChangedEvent $event
     *
     * @return void
     */
    public function handle(PasswordChangedEvent $event): void
    {
        $passwordChangeNotification = config('lara_auth.password_reset.password_changed_notification');

        $notification = (new $passwordChangeNotification())
            ->delay(now()->addSeconds(20));
        $event->getUser()->notify($notification);
    }
}
