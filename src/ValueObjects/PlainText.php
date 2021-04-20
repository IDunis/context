<?php

namespace Idunis\EventSauce\ValueObjects;

use InvalidArgumentException;

class PlainText extends BaseValueObject
{
    private int $length = 0;

    private function __construct($value, $length)
    {
        $this->assertValueNotEmpty($value);
        
        if (! empty($length)) {
            $this->assertValueAllowLimit($value, $length);
        }

        $this->length = $length;
        $this->value = $value;
    }

    public static function from($value, $length = 0)
    {
        return new self($value, $length);
    }

    /**
     * @param $value
     * @return self
     */
    public function change($value)
    {
        return new self($value, $this->length);
    }
    
    private function assertValueNotEmpty($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Text must be not empty.", 422);
        }
    }
    
    private function assertValueAllowLimit($value, $length)
    {
        if (strlen($value) > $length) {
            throw new InvalidArgumentException("Text must be not greater than {$length}.", 422);
        }
    }
}