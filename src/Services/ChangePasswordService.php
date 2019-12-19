<?php

declare(strict_types = 1);

namespace UonSoftware\LaraAuth\Services;

use UonSoftware\LaraAuth\Dto\PasswordReset;
use UonSoftware\LaraAuth\Contracts\ChangePasswordContract;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;

/**
 * Class ChangePasswordService
 *
 * @package UonSoftware\LaraAuth\Services
 */
class ChangePasswordService implements ChangePasswordContract
{
    /**
     * @var UpdateUserPasswordContract
     */
    protected $userPasswordContract;

    public function __construct(UpdateUserPasswordContract $userPasswordContract) {
        $this->userPasswordContract = $userPasswordContract;
    }

    /**
     * @inheritDoc
     *
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     *
     * @param \UonSoftware\LaraAuth\Dto\PasswordReset $passwordReset
     *
     */
    public function changePassword(PasswordReset $passwordReset): void
    {
        if (!$this->userPasswordContract->updatePassword($passwordReset->user, $passwordReset->password)) {
            throw new PasswordUpdateException();
        }
    }
}
