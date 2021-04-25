<?php

declare(strict_types=1);

namespace Idunis\Context\Providers;

use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Idunis\Context\Console\GenerateCommand;
use Idunis\Context\Console\MakeAggregateRootCommand;
use Idunis\Context\Console\MakeConsumerCommand;
use Illuminate\Support\ServiceProvider;

final class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([
                __DIR__.'/../../config/eventsauce.php' => $this->app->configPath('eventsauce.php'),
            ], ['eventsauce', 'eventsauce-config']);

            $this->publishes([
                __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
            ], ['eventsauce', 'eventsauce-migrations']);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('eventsauce');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/eventsauce.php', 'eventsauce');

        $this->commands([
            GenerateCommand::class,
            MakeAggregateRootCommand::class,
            MakeConsumerCommand::class,
        ]);

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });
    }

    public function provides()
    {
        return [
            GenerateCommand::class,
            MakeAggregateRootCommand::class,
            MakeConsumerCommand::class,
        ];
    }
}
