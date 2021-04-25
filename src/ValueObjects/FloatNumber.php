<?php

declare(strict_types=1);

namespace Idunis\Context\ValueObjects;

use InvalidArgumentException;

class FloatNumber extends BaseValueObject implements ValueObject
{
    private function __construct($value)
    {
        $this->assertValueMustBeNumber($value);
        
        $this->value = $value;
    }

    public static function from($value)
    {
        return new self($value);
    }

    /**
     * @param $value
     * @return self
     */
    public function change($value)
    {
        return new self($value);
    }
    
    protected function assertValueMustBeNumber($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Number must be a float.", 422);
        }
    }
}