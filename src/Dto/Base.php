<?php


namespace UonSoftware\LaraAuth\Dto;


use TypeError;

abstract class Base
{
    /**
     * Base constructor.
     *
     * @param  array  $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->__set($property, $value);
        }
    }

    /**
     * @param  string  $name
     * @param  string  $value
     *
     * @throws \TypeError
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            throw new TypeError('Property '.$name.' doesn\'t exist');
        }
    }


    /**
     * @param  string  $name
     *
     * @return mixed|null
     * @throws \TypeError
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new TypeError('Property '.$name.' doesn\'t exist');
    }
}
