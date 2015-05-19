<?php
namespace Genentech\CdnViews\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Genentech\CdnViews\Conversion\CdnHelper;

class UseCDN
{
    public function handle($request, Closure $next)
    {
        if ( ! Config::get('laravel5-cdn-views.enabled')) {
            return $next($request);
        }

        $cdn_url = Config::get('laravel5-cdn-views.cdn_url');
        $valid_tags = Config::get('laravel5-cdn-views.tags');
        $ssl_enabled = Config::get('laravel5-cdn-views.ssl_enabled');
        $cdn_helper = new CdnHelper($request, $cdn_url, $valid_tags, $ssl_enabled);

        $disabled_routes = Config::get('laravel5-cdn-views.disabled_routes');
        foreach($disabled_routes as $route) {
            $cdn_helper->blacklistRoute($route);
        }

        $response = $next($request);
        $content = $response->getOriginalContent();
        $cdn_content = $cdn_helper->convertPageForCDN($content);
        $response->setContent($cdn_content);

        return $response;
    }
}
