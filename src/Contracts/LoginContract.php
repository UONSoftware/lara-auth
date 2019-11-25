<?php

namespace UonSoftware\LaraAuth\Contracts;

use Closure;

interface LoginContract
{
    
    
    /**
     * @param  array  $login
     * @param  \Closure|null  $additionalChecks
     *
     * @return array
     * @throws \UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException
     * @throws \UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     */
    public function login(array $login, ?Closure $additionalChecks = null): array;
}
