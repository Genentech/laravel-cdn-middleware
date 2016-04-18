<?php
namespace Genentech\CdnViews\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Genentech\CdnViews\Conversion\CdnHelper;

class UseCDN
{
    public function handle(Request $request, Closure $next)
    {
        if ( ! Config::get('laravel5-cdn-views.enabled')) {
            return $next($request);
        }

        $cdn_url = Config::get('laravel5-cdn-views.cdn_url');
        $valid_tags = Config::get('laravel5-cdn-views.tags');
        $cdn_helper = new CdnHelper($cdn_url, $valid_tags);

        $ssl_enabled = Config::get('laravel5-cdn-views.ssl_enabled');
        if ( ! $ssl_enabled && $request->isSecure()) {
            return $next($request);
        }

        $disabled_routes = Config::get('laravel5-cdn-views.disabled_routes');
        foreach ($disabled_routes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        $response = $next($request);
        try {
            if (method_exists($response, 'getOriginalContent')) {
                $content = $response->getOriginalContent();
                if (method_exists($content, 'render')) {
                    $content = $content->render();
                    $cdn_content = $cdn_helper->convertPageForCDN($content);
                    $response->setContent($cdn_content);
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return $response;
    }
}
