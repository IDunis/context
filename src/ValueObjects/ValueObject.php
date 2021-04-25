<?php 

declare(strict_types=1);

namespace Idunis\Context\ValueObjects;

interface ValueObject
{

    /**
     * Gets the value.
     *
     * @return mixed
     */
    public function getValue();


    /**
     * Gets the value as a string representation.
     *
     * @return string
     */
    public function toString() : string ;

    /**
     * Determines whether this and the other value objects
     * have the same value.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function sameValueAs(ValueObject $other) : bool ;
}