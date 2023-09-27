<?php

namespace Laxity7\LaravelSwagger;

use Illuminate\Support\ServiceProvider;

final class SwaggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSwaggerDoc::class,
            ]);
        }

        $source = __DIR__.'/../config/laravel-swagger.php';

        $this->publishes([
            $source => config_path('laravel-swagger.php'),
        ]);

        $this->mergeConfigFrom($source, 'laravel-swagger');

        $this->app->bind(Generator::class, function () {
            return new Generator(config('laravel-swagger'));
        });
    }
}
