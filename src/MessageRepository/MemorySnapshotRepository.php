<?php

declare(strict_types=1);

namespace Idunis\EventSauce\MessageRepository;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;

final class MemorySnapshotRepository implements SnapshotRepository
{
    /**
     * @var array<string,Snapshot>
     */
    private $snapshots = [];

    public function persist(Snapshot $snapshot): void
    {
        $this->snapshots[$snapshot->aggregateRootId()->toString()] = $snapshot;
    }

    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        return $this->snapshots[$id->toString()] ?? null;
    }
}
