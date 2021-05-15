<?php

declare(strict_types=1);

namespace Idunis\Context\Consumers;

use EventSauce\EventSourcing\Consumer as EventSauceConsumer;
use EventSauce\EventSourcing\Message;

abstract class Consumer implements EventSauceConsumer
{
    public function handle(Message $message): void
    {
        $event = $message->event();
        $parts = explode('\\', get_class($event));
        $method = 'on'.end($parts);

        if (method_exists($this, $method)) {
            $this->{$method}($event, $message->aggregateRootId());
        }
    }
}
