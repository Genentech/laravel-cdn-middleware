<?php
namespace Genentech\CdnViews;

use Genentech\CdnViews\Views\CdnViewFactory;
use Illuminate\Support\ServiceProvider;

final class CdnViewServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel5-cdn-views.php' => config_path('laravel5-cdn-views.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $blade_enabled = $this->app['config']['laravel5-cdn-views.blade_enabled'];
        $cdn_enabled = $this->app['config']['laravel5-cdn-views.enabled'];
        if ($cdn_enabled && $blade_enabled) {
            $this->extendViews();
        }
    }

    protected function extendViews() {
        $this->app->extend('view', function () {
            $app = app();

            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $env = new CdnViewFactory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $env->setContainer($app);

            $env->share('app', $app);

            return $env;
        });
    }
}
