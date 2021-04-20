<?php

namespace Idunis\EventSauce\AggregateRoots;

use EventSauce\EventSourcing\AggregateRoot as EventSauceAggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use Generator;

abstract class AggregateRoot implements EventSauceAggregateRoot
{
    /**
     * @var AggregateRootId
     */
    private $aggregateRootId;

    /**
     * @var int
     */
    private $aggregateRootVersion = 0;

    /**
     * @var object[]
     */
    private $recordedEvents = [];

    private function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    /**
     * @see AggregateRoot::aggregateRootVersion
     */
    public function aggregateRootVersion(): int
    {
        return $this->aggregateRootVersion;
    }

    protected function apply(object $event): void
    {
        $parts = explode('\\', get_class($event));

        $method = 'apply'.end($parts);

        if (method_exists($this, $method)) {
            $this->$method($event);

            $this->aggregateRootVersion++;
        }
    }

    protected function recordThat(object $event): void
    {
        $this->apply($event);
        $this->recordedEvents[] = $event;
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;
        $this->recordedEvents = [];

        return $releasedEvents;
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @param Generator $events
     * @return AggregateRoot
     * @see AggregateRoot::reconstituteFromEvents
     */
    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events): AggregateRoot
    {
        /** @var AggregateRoot&\EventSauce\EventSourcing\AggregateRootBehaviour $aggregateRoot */
        $aggregateRoot = new static($aggregateRootId);

        /** @var object $event */
        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRoot->aggregateRootVersion = $events->getReturn() ?: 0;

        /* @var AggregateRoot $aggregateRoot */
        return $aggregateRoot;
    }
}
