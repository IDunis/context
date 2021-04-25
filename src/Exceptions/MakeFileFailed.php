<?php

declare(strict_types=1);

namespace Idunis\Context\Exceptions;

use Exception;

class MakeFileFailed extends Exception
{
    public static function fileExists(string $path): self
    {
        return new static("The file at path `{$path}` already exists.");
    }
}
