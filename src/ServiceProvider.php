<?php
declare(strict_types=1);

namespace OrangeShadow\ElasticFilter;

use OrangeShadow\ElasticFilter\Console\Commands\ElasticDataIndex;

class ServiceProvider  extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->publishes([
            __DIR__.'/config/elastic_filter.php' => config_path('elastic_filter.php'),
        ],'config');

        $this->publishes([
            __DIR__.'/config/indexes' => config_path('indexes'),
        ],'config');

        $this->commands([
            ElasticDataIndex::class
        ]);

    }
}
