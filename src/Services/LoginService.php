<?php

namespace UonSoftware\LaraAuth\Services;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Contracts\Hashing\Hasher;
use UonSoftware\LaraAuth\Events\LoginEvent;
use UonSoftware\LaraAuth\Http\Resources\User;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use Illuminate\Contracts\Config\Repository as Config;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use UonSoftware\RefreshTokens\Contracts\RefreshTokenGenerator;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;
use UonSoftware\RefreshTokens\Contracts\RefreshTokenRepository;
use UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException;
use UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException;


/**
 * Class LoginService
 *
 * @package UonSoftware\LaraAuth\Services
 */
class LoginService implements LoginContract
{
    /**
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * @var \UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract
     */
    protected $passwordService;

    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

    /**
     * @var \UonSoftware\RefreshTokens\Contracts\RefreshTokenGenerator
     */
    protected $refreshTokenGenerator;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $eventDispatcher;


    /**
     * LoginService constructor.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher                       $hasher
     * @param \UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract $passwordContract
     * @param \Tymon\JWTAuth\JWTAuth                                     $jwtAuth
     * @param RefreshTokenGenerator                                      $refreshTokenGenerator
     * @param \Illuminate\Contracts\Config\Repository                    $config
     * @param \Illuminate\Contracts\Events\Dispatcher                    $eventDispatcher
     */
    public function __construct(
        Hasher $hasher,
        UpdateUserPasswordContract $passwordContract,
        JWTAuth $jwtAuth,
        RefreshTokenGenerator $refreshTokenGenerator,
        Config $config,
        EventDispatcher $eventDispatcher
    ) {
        $this->hasher = $hasher;
        $this->passwordService = $passwordContract;
        $this->jwtAuth = $jwtAuth;
        $this->refreshTokenGenerator = $refreshTokenGenerator;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Throwable
     * @throws \UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException
     * @throws \UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     * @throws \UonSoftware\RsaSigner\Exceptions\SignatureCorrupted
     *
     * @param \Closure|null $additionalChecks
     *
     * @param array         $login
     *
     * @return array
     */
    public function login(array $login, ?Closure $additionalChecks = null): array
    {
        $findUser = $this->config
            ->get('lara_auth.user.search');


        $passwordOnModel = $this->config
            ->get('lara_auth.user.password.field_on_model');
        $passwordOnRequest = $this->config
            ->get('lara_auth.user.password.field_from_request');

        $where = [];
        foreach ($findUser as $search) {
            ['field' => $field, 'operator' => $operator] = $search;
            $where[] = [$field, $operator, $login[$field]];
        }
        $userModel = $config['user_model'] ?? '\App\User';
        $user = $userModel::query()
            ->where($where)
            ->firstOrFail();

        if (!$this->hasher->check($login[$passwordOnRequest], $user->{$passwordOnModel})) {
            throw new InvalidCredentialsException();
        }

        // Check if hash is still good
        if ($this->hasher->needsRehash($user->{$passwordOnModel}) && !$this->passwordService->updatePassword(
                $user,
                $login[$passwordOnRequest]
            )) {
            throw new PasswordUpdateException();
        }

        $emailVerificationField = $this->config->get('lara_auth.user.email_verification.field');
        $shouldCheckEmailVerification = $this->config->get('lara_auth.user.email_verification.check');

        if ($shouldCheckEmailVerification === true && $user->{$emailVerificationField} === null) {
            throw new EmailIsNotVerifiedException();
        }

        if ($additionalChecks !== null) {
            $additionalChecks($user);
        }

        [1 => $refreshToken] = $this->refreshTokenGenerator->generateNewRefreshToken(
            null,
            $user->getAuthIdentifier()
        );
        $userResource = $this->config->get('lara_auth.user_resource') ?? User::class;
        $this->eventDispatcher->dispatch(new LoginEvent($user));
        return [
            'user' => new $userResource($user),
            'auth' => [
                'token'   => $this->jwtAuth->fromSubject($user),
                'refresh' => $refreshToken,
                'type'    => 'Bearer',
            ],
        ];
    }

}
