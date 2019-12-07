<?php

namespace UonSoftware\LaraAuth\Http\Controllers;

use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Manager as JwtManager;
use UonSoftware\LaraAuth\Dto\PasswordReset;
use Tymon\JWTAuth\Exceptions\PayloadException;
use Illuminate\Contracts\Config\Repository as Config;
use UonSoftware\LaraAuth\Events\RequestNewPasswordEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use UonSoftware\LaraAuth\Http\Requests\NewPasswordRequest;
use UonSoftware\LaraAuth\Contracts\ChangePasswordContract;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\LaraAuth\Http\Requests\ChangePasswordRequest;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

class ChangePasswordController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $eventDispatcher;

    /**
     * @var \UonSoftware\LaraAuth\Contracts\ChangePasswordContract
     */
    private $changePasswordService;

    /**
     * @var \Tymon\JWTAuth\Manager
     */
    private $jwtManager;

    /**
     * @var \Tymon\JWTAuth\Factory
     */
    private $payloadFactory;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    public function __construct(
        EventDispatcher $eventDispatcher,
        ChangePasswordContract $changePasswordService,
        JwtManager $manager,
        Config $config
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->changePasswordService = $changePasswordService;
        $this->jwtManager = $manager;
        $this->payloadFactory = $manager->getPayloadFactory();
        $this->config = $config;
    }

    public function requestNewPassword(NewPasswordRequest $request)
    {
        $email = $request->input('email');
        $userModel = $this->config->get('lara_auth.user_model');
        try {
            $userModel::query()
                ->where('email', '=', $email)
                ->firstOrFail();

            event(new RequestNewPasswordEvent($email));
            return response()->json(['message' => 'Your email has been sent'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'message' => 'User with email ' . $email . ' is not found',
                ],
                404
            );
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $passwordDto = new PasswordReset(
            [
                'user'     => $request->user(),
                'password' => $request->input('password'),
            ]
        );

        try {
            $this->changePasswordService->changePassword($passwordDto);

            return response()->json(
                [
                    'message' => 'Your password has been changed successfully',
                ]
            );
        } catch (PasswordUpdateException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        } catch (PayloadException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
