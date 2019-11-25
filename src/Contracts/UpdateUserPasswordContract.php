<?php


namespace UonSoftware\LaraAuth\Contracts;


/**
 * Interface UpdateUserPasswordContract
 *
 * @package UonSoftware\LaraAuth\Contracts
 */
interface UpdateUserPasswordContract
{
    /**
     * @param \App\User|integer|string $user
     * @param  string  $newPassword
     *
     * @return bool
     */
    public function updatePassword($user, string $newPassword): bool;
}
