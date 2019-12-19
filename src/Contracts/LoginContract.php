<?php

namespace UonSoftware\LaraAuth\Contracts;

use Closure;

interface LoginContract
{
    /**
     * This method will login the user with given parameters
     * This method will return array with two keys 'auth' and 'user'
     *
     * 'auth' key will contain auth token, refresh token and the type of the authentication (Bearer)
     * 'user' key will contain everything defined in configuration option `lara_auth.serialization_fields`
     *
     * This method is safe to send it to the response class as long as you dont put
     * sensitive data in serialization fields in configuration file
     *
     * Events:
     *   - LoginEvent::class
     *
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
    public function login(array $login, ?Closure $additionalChecks = null): array;
}
