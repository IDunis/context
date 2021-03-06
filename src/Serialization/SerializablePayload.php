<?php

declare(strict_types=1);

namespace Idunis\Context\Serialization;

interface SerializablePayload
{
    public function toPayload(): array;

    public static function fromPayload(array $payload): SerializablePayload;
}
