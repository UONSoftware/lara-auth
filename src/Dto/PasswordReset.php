<?php


namespace UonSoftware\LaraAuth\Dto;


use UonSolutions\LaraAuth\Dto\Base;

/**
 * Class PasswordReset
 *
 * @package UonSoftware\LaraAuth\Dto
 * @property string    $password
 * @property \App\User $user
 */
class PasswordReset extends Base
{
    /**
     * @var string
     */
    protected $password;

    protected $user;

}
