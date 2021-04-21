<?php

declare(strict_types=1);

namespace Idunis\EventSauce\AggregateRoots;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
// use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use Idunis\EventSauce\Message\EventMessageDispatcher;
use Idunis\EventSauce\Message\MessageDispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use LogicException;

abstract class AggregateRootRepository implements AggregateRootRepositoryWithSnapshotting
{
    protected string $aggregateRoot = '';

    protected array $consumers = [];

    protected string $connection = '';

    protected string $table = '';

    protected string $snapshotTable = '';

    protected string $queue = '';
    
    protected string $messageRepository = '';
    
    protected string $snapshotRepository = '';

    protected static string $inputFile = '';

    protected static string $outputFile = '';

    public function __construct()
    {
        if (! is_a($this->aggregateRoot, AggregateRoot::class, true)) {
            throw new LogicException('You have to set an aggregate root before the repository can be initialized.');
        }
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->repository()->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot)
    {
        $this->repository()->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->repository()->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        return $this->repository()->retrieveFromSnapshot($aggregateRootId);
    }

    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        $this->repository()->storeSnapshot($aggregateRoot);
    }

    private function repository()
    {
        $aggregateRepository = new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            new MessageDispatcherChain(
                $this->buildMessageDispatcher(),
                new EventMessageDispatcher()
            )
        );

        return new ConstructingAggregateRootRepositoryWithSnapshotting(
            $this->aggregateRoot,
            $this->getMessageRepository(),
            $this->getSnapshotRepository(),
            $aggregateRepository
        );
    }

    public static function inputFile(): string
    {
        return static::$inputFile;
    }

    public static function outputFile(): string
    {
        return static::$outputFile;
    }

    private function buildMessageDispatcher(): MessageDispatcher
    {
        $dispatcher = new MessageDispatcher(
            ...$this->consumers,
        );

        if ($this->queue) {
            $dispatcher->onQueue($this->queue);
        }

        return $dispatcher;
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        $connection = $this->connection
            ?: config('eventsauce.connection')
            ?: config('database.default');

        return DB::connection($connection);
    }

    /**
     * @return MessageRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getMessageRepository(): MessageRepository
    {
        $messageRepository = $this->messageRepository ?: config('eventsauce.message_repository');

        return app()->make($messageRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->table ?: config('eventsauce.table'),
        ]);
    }

    /**
     * @return SnapshotRepository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getSnapshotRepository(): SnapshotRepository
    {
        $snapshotRepository = $this->snapshotRepository ?: config('eventsauce.snapshot_repository');
        
        return app()->make($snapshotRepository, [
            'connection'    =>  $this->getConnection(),
            'table'         =>  $this->snapshotTable ?: config('eventsauce.snapshot_table'),
        ]);
    }
}
