<?php

namespace Idunis\EventSauce\ValueObjects;

abstract class BaseValueObject
{

    /**
     * @var mixed
     */
    protected $value;


    /**
     * Gets the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Gets the value as a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->getValue();
    }


    /**
     * Determines whether this and the other value objects
     * have the same value.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function sameValueAs(ValueObject $other) : bool
    {
        return $this->getValue() == $other->getValue();
    }
}