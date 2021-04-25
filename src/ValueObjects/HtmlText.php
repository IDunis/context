<?php

declare(strict_types=1);

namespace Idunis\Context\ValueObjects;

use InvalidArgumentException;

class HtmlText extends BaseValueObject implements ValueObject
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
     * Gets the html content as a string.
     *
     * @return string
     */
    public function toText(): string
    {
        return (string)$this->getValue();
    }

    /**
     * @param $value
     * @return self
     */
    public function change($value)
    {
        return new self($value, $this->length);
    }
    
    protected function assertValueNotEmpty($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Content must be not empty.", 422);
        }
    }
    
    protected function assertValueAllowLimit($value, $length)
    {
        if (strlen($value) > $length) {
            throw new InvalidArgumentException("Content must be not greater than {$length}.", 422);
        }
    }
}