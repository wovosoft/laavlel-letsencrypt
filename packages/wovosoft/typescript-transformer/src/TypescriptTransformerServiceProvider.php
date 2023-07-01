<?php

namespace Wovosoft\TypescriptTransformer;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Wovosoft\TypescriptTransformer\Commands\TypescriptModelTransformer;

class TypescriptTransformerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'wovosoft');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'wovosoft');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/typescript-transformer.php', 'typescript-transformer');

        // Register the service the package provides.
//        $this->app->singleton('typescript-transformer', function ($app) {
//            return new TypescriptTransformer(models:[
//                User::class
//            ]);
//        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['typescript-transformer'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/typescript-transformer.php' => config_path('typescript-transformer.php'),
        ], 'typescript-transformer.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/wovosoft'),
        ], 'typescript-transformer.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/wovosoft'),
        ], 'typescript-transformer.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/wovosoft'),
        ], 'typescript-transformer.views');*/

        // Registering package commands.
        $this->commands([
            TypescriptModelTransformer::class
        ]);
    }
}
