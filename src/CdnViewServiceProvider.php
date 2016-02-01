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

    }
}
