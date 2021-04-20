<?php

declare(strict_types=1);

namespace Idunis\EventSauce\Message;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher as EventSauceMessageDispatcher;
use Idunis\EventSauce\Consumers\HandleConsumer;
use Illuminate\Contracts\Queue\ShouldQueue;

final class MessageDispatcher implements EventSauceMessageDispatcher
{
    /** @var string[] */
    private $consumers;

    private string $queue = '';

    public function __construct(string ...$consumers)
    {
        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages)
    {
        foreach ($this->consumers as $consumer) {
            if (is_a($consumer, ShouldQueue::class, true)) {
                $dispatch = dispatch(new HandleConsumer($consumer, ...$messages));

                if ($this->queue) {
                    $dispatch->onQueue($this->queue);
                }
            } else {
                dispatch_now(new HandleConsumer($consumer, ...$messages));
            }
        }
    }

    public function onQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }
}
