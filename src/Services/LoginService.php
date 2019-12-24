<?php

namespace UonSoftware\LaraAuth\Services;

use Closure;
use TypeError;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Hashing\Hasher;
use UonSoftware\LaraAuth\Events\LoginEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use Illuminate\Contracts\Config\Repository as Config;
use UonSoftware\LaraAuth\Http\Resources\User as UserResource;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use UonSoftware\RefreshTokens\Contracts\RefreshTokenGenerator;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;
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
     * {@inheritDoc}
     */
    public function login(array $login, ?Closure $additionalChecks = null): array
    {
        $user = $this->getUser($login);

        $this->checkPassword($user, $login);

        $this->checkEmailVerification($user);

        if ($additionalChecks !== null) {
            $additionalChecks($user);
        }

        [1 => $refreshToken] = $this->refreshTokenGenerator->generateNewRefreshToken(
            null,
            $user->getAuthIdentifier()
        );
        $userResource = $this->config->get('lara_auth.user_resource') ?? UserResource::class;
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

    /**
     * This method gets the user based on the filters provided in the config file
     * if user is not found exception will be thrown
     *
     * ** For this method to work user must be instance of Authenticatable::class and JWTSubject::class **
     * ** If user is not instance of both of those classes then the TypeError will be raised **
     * ** Those interfaces are needed for authentication and token creation **
     *
     * @throws \Throwable
     *
     * @param array $login
     *
     * @return Authenticatable&JWTSubject
     */
    protected function getUser(array $login)
    {
        $filters = $this->config
            ->get('lara_auth.user.search');
        $where = [];
        foreach ($filters as $filter) {
            ['field' => $field, 'operator' => $operator] = $filter;
            $where[] = [$field, $operator, $login[$field]];
        }
        $userModel = $config['user_model'] ?? '\App\User';

        /**  $user */
        $user = $userModel::query()
            ->where($where)
            ->firstOrFail();

        $userType = !($user instanceof Authenticatable) && !($user instanceof JWTSubject);
        $message = 'User must be instance of Authenticatable and JWTSubject';

        throw_if($userType, TypeError::class, $message);

        return $user;
    }

    /**
     * Comparing password with the input and database hash
     * if password is ok then this method will perform the checking if the
     * database hash needs rehashing, if it needs rehashing it will be rehashed
     *
     * @throws \UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     *
     * @param Authenticatable&JWTSubject $user
     * @param array                      $login
     *
     * @retrun void
     */
    protected function checkPassword($user, array $login): void
    {
        $passwordOnModel = $this->config
            ->get('lara_auth.user.password.field_on_model');
        $passwordOnRequest = $this->config
            ->get('lara_auth.user.password.field_from_request');

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
    }

    /**
     * This method checks if email is verified
     * For this method to work 'lara_auth.user.email_verification.check' config setting
     * must be set to true, otherwise check will not be performed
     *
     * @throws \UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException
     *
     * @param Authenticatable&JWTSubject $user
     *
     * @return void
     */
    protected function checkEmailVerification($user): void
    {
        $emailVerificationField = $this->config->get('lara_auth.user.email_verification.field');
        $shouldCheckEmailVerification = $this->config->get('lara_auth.user.email_verification.check');

        if ($shouldCheckEmailVerification === true && $user->{$emailVerificationField} === null) {
            throw new EmailIsNotVerifiedException();
        }
    }
}
