<?php

declare(strict_types=1);

namespace Idunis\Context\MessageRepository;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\Connection;
use Ramsey\Uuid\Uuid;

class StoredMessageRepository implements MessageRepository
{
    /** @var Connection */
    protected $connection;

    /** @var string */
    protected $table;

    /** @var MessageSerializer */
    protected $serializer;

    public function __construct(Connection $connection, string $table, MessageSerializer $serializer)
    {
        $this->connection = $connection;

        $this->table = $table;

        $this->serializer = $serializer;
    }

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serializedMessage = $this->serializer->serializeMessage($message);
            $headers = $serializedMessage['headers'];

            $this->connection
                ->table($this->table)
                ->insert([
                    'event_id'                  =>  $headers[Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                    'event_type'                =>  $headers[Header::EVENT_TYPE],
                    'aggregate_root_id'         =>  $headers[Header::AGGREGATE_ROOT_ID] ?? null,
                    'aggregate_root_version'    =>  $headers[Header::AGGREGATE_ROOT_VERSION] ?? null,
                    'recorded_at'               =>  $headers[Header::TIME_OF_RECORDING],
                    'payload'                   =>  json_encode($serializedMessage),
                ]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->connection
            ->table($this->table)
            ->select('payload')
            ->where('aggregate_root_id', $id->toString())
            ->orderBy('recorded_at')
            ->get();

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }

    public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator
    {
        $payloads = $this->connection
            ->table($this->table)
            ->select('payload')
            ->where('aggregate_root_id', $id->toString())
            ->where('aggregate_root_version', '>', $aggregateRootVersion)
            ->orderBy('recorded_at')
            ->get();

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }
}
