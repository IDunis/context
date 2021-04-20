<?php

declare(strict_types=1);

namespace Idunis\EventSauce\AggregateRoots;

use EventSauce\EventSourcing\AggregateRootId as EventSauceAggregateRootId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class AggregateRootId implements EventSauceAggregateRootId
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function toString(): string
    {
        return $this->identifier;
    }

    public function toUuid(): UuidInterface
    {
        return Uuid::fromString($this->identifier);
    }

    public static function create(): AggregateRootId
    {
        return new AggregateRootId(Uuid::uuid4()->toString());
    }

    /**
     * @return static
     */
    public static function fromString(string $aggregateRootId): EventSauceAggregateRootId
    {
        return new static($aggregateRootId);
    }
}
