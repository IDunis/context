<?php

declare(strict_types=1);

namespace Idunis\EventSauce;

use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Idunis\EventSauce\Console\GenerateCommand;
use Idunis\EventSauce\Console\MakeAggregateRootCommand;
use Idunis\EventSauce\Console\MakeConsumerCommand;
use Illuminate\Support\ServiceProvider;

final class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eventsauce.php' => $this->app->configPath('eventsauce.php'),
            ], ['eventsauce', 'eventsauce-config']);

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], ['eventsauce', 'eventsauce-migrations']);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eventsauce.php', 'eventsauce');

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
