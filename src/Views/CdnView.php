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

    function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = array())
    {
        parent::__construct($factory, $engine, $view, $path, $data = array());
        $request = App::make('request');
        $cdn_url = Config::get('laravel5-cdn-views.cdn-url');
        $valid_tags = Config::get('laravel5-cdn-views.tags');
        $ssl_enabled = Config::get('laravel5-cdn-views.ssl_enabled');
        $this->cdn_helper = new CdnHelper($request, $cdn_url, $valid_tags, $ssl_enabled);
    }


    public function render(Closure $callback = null)
    {
        $contents = parent::render($callback);

        // only CDNify assets if on final pass of rendering
        if ($this->factory->doneRendering()) {
            $contents = $this->cdn_helper->convertContentForCDN($contents);
        }

        return $contents;
    }
}
