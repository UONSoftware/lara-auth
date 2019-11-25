<?php


namespace UonSoftware\LaraAuth\Exceptions;

use Exception;
use Throwable;


class EmailIsNotVerifiedException extends Exception
{
    public function __construct($message = 'Email is not verified', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
