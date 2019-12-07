<?php


namespace UonSoftware\LaraAuth\Exceptions;


use Exception;
use Throwable;

class NullReferenceException extends Exception
{
    public function __construct($message = 'Pointer to null has been dereferenced', $code = 0, Throwable $previous =
    null)
    {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}