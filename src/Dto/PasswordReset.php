<?php


namespace UonSoftware\LaraAuth\Dto;


use TypeError;

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

    /**
     * Base constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->__set($property, $value);
        }
    }

    /**
     * @throws \TypeError
     *
     * @param string $value
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            throw new TypeError('Property ' . $name . ' doesn\'t exist');
        }
    }


    /**
     * @throws \TypeError
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new TypeError('Property ' . $name . ' doesn\'t exist');
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }

}
