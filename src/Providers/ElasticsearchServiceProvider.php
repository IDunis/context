<?php

declare(strict_types=1);

namespace Idunis\Context\Providers;

use Idunis\Context\ORM\Eloquent\Elasticsearch\Factory;
use Idunis\Context\ORM\Eloquent\Elasticsearch\Manager;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setUpConfig();
        $this->setUpConsoleCommands();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $app->singleton(Factory::class, function($app) {
            return new Factory();
        });

        $app->singleton(Manager::class, function($app) {
            return new Manager($app, $app[Factory::class]);
        });

        // $app->alias('elasticsearch', Manager::class);
    }

    protected function setUpConfig(): void
    {
        $source = dirname(__DIR__) . '/../../config/elasticsearch.php';

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('elasticsearch.php')], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('elasticsearch');
        }

        $this->mergeConfigFrom($source, 'elasticsearch');
    }

    private function setUpConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            
        }
    }
}