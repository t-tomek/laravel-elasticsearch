<?php

namespace T2\ElasticLaravel;

use T2\ElasticLaravel\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class ElasticSearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->resolving('db', function ($db) {
            $db->extend('elasticsearch', function ($config, $name) {
                $config['name'] = $name;
                return new Connection($config);
            });
        });
    }
}
