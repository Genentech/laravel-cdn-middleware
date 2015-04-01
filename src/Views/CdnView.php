<?php
namespace Genentech\CdnViews\Views;

use Closure;
use Genentech\CdnViews\Conversion\CdnHelper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\View\Factory;
use Illuminate\View\View;

class CdnView extends View
{
    protected $cdn_helper;

    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = array())
    {
        parent::__construct($factory, $engine, $view, $path, $data);

        $this->cdn_helper = new CdnHelper(
            App::make('request'),
            Config::get('laravel5-cdn-views.cdn_url'),
            Config::get('laravel5-cdn-views.tags'),
            Config::get('laravel5-cdn-views.ssl_enabled')
        );

        $disabled_routes = Config::get('laravel5-cdn-views.disabled_routes');

        foreach ($disabled_routes as $route) {
            $this->cdn_helper->blacklistRoute($route);
        }
    }

    public function render(Closure $callback = null)
    {
        $contents = parent::render($callback);

        // only CDNify assets if on final pass of rendering
        if ($this->factory->doneRendering()) {
            $contents = $this->cdn_helper->convertPageForCDN($contents);
        }

        return $contents;
    }
}
