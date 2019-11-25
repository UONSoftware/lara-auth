<?php


namespace UonSoftware\LaraAuth\Exceptions;

use Exception;


class AccountDisabledException extends Exception
{
    public function __construct(string $message = 'User account is disabled')
    {
        parent::__construct($message);
    }
}
