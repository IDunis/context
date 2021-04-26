<?php

declare(strict_types=1);

namespace Idunis\Context\ValueObjects;

use InvalidArgumentException;

class Status extends BaseValueObject implements ValueObject
{
    private function __construct($value)
    {
        $this->assertValueNotEmpty($value);
        
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
    
    protected function assertValueNotEmpty($value)
    {
        if (!in_array($value, [0, 1, false, true])) {
            throw new InvalidArgumentException("Status must be [0, 1, false, true].", 422);
        }
    }
}