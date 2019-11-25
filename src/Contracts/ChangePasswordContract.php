<?php


namespace UonSoftware\LaraAuth\Contracts;


use UonSoftware\LaraAuth\Dto\PasswordReset;

interface ChangePasswordContract
{
    /**
     * @param  \UonSoftware\LaraAuth\Dto\PasswordReset  $passwordReset
     *
     * @throws \Tymon\JWTAuth\Exceptions\PayloadException
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     * @return mixed
     */
    public function changePassword(PasswordReset $passwordReset);
}
