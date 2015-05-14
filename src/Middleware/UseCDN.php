<?php
namespace Genentech\CdnViews\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Genentech\CdnViews\Conversion\CdnHelper;

class UseCDN
{
    public function handle($request, Closure $next)
    {
        $cdn_url = Config::get('laravel5-cdn-views.cdn_url');
        $valid_tags = Config::get('laravel5-cdn-views.tags');
        $ssl_enabled = Config::get('laravel5-cdn-views.ssl_enabled');
        $cdn_helper = new CdnHelper($request, $cdn_url, $valid_tags, $ssl_enabled);

        $disabled_routes = Config::get('laravel5-cdn-views.disabled_routes');
        foreach($disabled_routes as $route) {
            $this->cdn_helper->blacklistRoute($route);
        }

        $content = $next($request);
        return $cdn_helper->convertPageForCDN($content);
    }
}
