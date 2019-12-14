<?php

namespace UonSoftware\LaraAuth\Listeners;

use Illuminate\Routing\UrlGenerator;
use Tymon\JWTAuth\Manager as JwtManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Config\Repository as Config;
use UonSoftware\LaraAuth\Events\RequestNewPasswordEvent;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use UonSoftware\LaraAuth\Notifications\PasswordChangeNotification;

class RequestNewPasswordListener implements ShouldQueue
{

    /**
     * @var \Tymon\JWTAuth\Manager
     */
    private $jwtManager;

    /**
     * @var \Tymon\JWTAuth\Factory
     */
    private $payloadFactory;

    /**
     * @var \Illuminate\Routing\UrlGenerator
     */
    private $urlGenerator;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $eventDispatcher;


    /**
     * Create the event listener.
     *
     * @param \Tymon\JWTAuth\Manager                  $manager
     * @param \Illuminate\Routing\UrlGenerator        $urlGenerator
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Events\Dispatcher $eventDispatcher
     */
    public function __construct(
        JwtManager $manager,
        UrlGenerator $urlGenerator,
        Config $config,
        EventDispatcher $eventDispatcher
    ) {
        $this->jwtManager = $manager;
        $this->payloadFactory = $manager->getPayloadFactory();
        $this->config = $config;
    }

    /**
     * Handle the event.
     *
     * @param \UonSoftware\LaraAuth\Events\RequestNewPasswordEvent $event
     *
     * @return void
     */
    public function handle(RequestNewPasswordEvent $event): void
    {
        [
            'base'            => $base,
            'change_password' => $route,
        ] = $this->config->get('lara_auth.password_reset.frontend_url');

        $ttl = $this->config->get('lara_auth.password_reset.ttl');
        $user = $event->getUser();
        $payload = $this->payloadFactory
            ->setTTL($ttl)
            ->customClaims(
                [
                    'email' => $user->email,
                    'sub'   => $user->getJWTIdentifier(),
                ]
            )
            ->make();

        $jwt = $this->jwtManager->encode($payload)->get();
        $passwordChangeNotification = $this->config->get('lara_auth.password_reset.request_notification');
        $url = $base . $route . '?access_token=' . $jwt;
        $notification = (new $passwordChangeNotification($url))
            ->delay(now()->addSeconds(10));

        $user->notify($notification);
    }
}
