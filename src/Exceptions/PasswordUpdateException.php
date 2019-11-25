<?php


namespace UonSoftware\LaraAuth\Exceptions;


use Throwable;
use Exception;

/**
 * Class PasswordUpdateException
 *
 * @package UonSoftware\LaraAuth\Exceptions
 */
class PasswordUpdateException extends Exception
{
    /**
     * PasswordUpdateException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct($message = 'Error while updating password', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
