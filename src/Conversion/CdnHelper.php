<?php
namespace Genentech\CdnViews\Conversion;

use App;
use Config;
use Masterminds\HTML5;
use DOMNode;

/**
 *  CDN Helper
 *
 *  Collection of methods to assist with the transformation of URLs into their CDN counterparts
 */
class CdnHelper
{
    protected $tagConverter;
    protected $cdnUrl;
    protected $valid_tags;

    function __construct()
    {
        $this->cdnUrl = Config::get('laravel5-cdn-views.cdn_url');
        $this->valid_tags = Config::get('laravel5-cdn-views.tags');
        $this->tagConverter = new TagConverter();
        $this->registerTags();
    }

    private function registerTags() {
        $url_conversion_function = self::convertURL;

        foreach($this->valid_tags as $tag) {
            $this->tagConverter->registerTag($tag, function(DOMNode $element) use ($url_conversion_function) {
                if ($element->hasAttribute("src")) {
                    $element->setAttribute("src",
                        $url_conversion_function($element->getAttribute("src"))
                    );
                }
                if ($element->hasAttribute("href")) {
                    $element->setAttribute("href",
                        $url_conversion_function($element->getAttribute("href"))
                    );
                }
                return $element;
            });
        }
    }

    /**
     * Convert Content For CDN
     *
     * This function converts content to be served by the CDN
     * It parses through the DOM for any of the set targets
     * and replaces their src and href with the correct versions
     *
     * @param  string $content The original content
     * @return string            The content via CDN
     */
    public function convertContentForCDN($content)
    {
        $html5 = new HTML5();
        $doc = $html5->loadHTML($content);

        foreach ($this->valid_tags as $target) {
            $nodes = $doc->getElementsByTagName($target);
            foreach ($nodes as $iterator => $element) {
                $converted = $this->tagConverter($element);
                $nodes->replaceChild($converted, $element);
            }
        }

        return $html5->saveHTML($doc);
    }

    /**
     * Convert URL
     *
     * This function converts a URL to be served via the CDN
     *
     * @param  string $url The URL to be converted
     * @return string        The URL with the correct cdn prepended
     */
    public function convertURL($url)
    {
        if ($this->shouldUseCDN()) {
            return $this->prependCDN($url, Config::get('laravel5-cdn-views.cdn_url'));
        }

        return $this->prependCDN($url, "");
    }

    /**
     * Prepend CDN
     *
     * This function turns a regular url into a CDN url
     *
     * @param  string $url The URL to be converted
     * @param  string $pull_url The URL to be prepended; defaults to gene.com pullzone
     * @return string             The URL with the cdn prepended
     */
    private static function prependCDN($url, $pull_url)
    {

        $request = App::make('request');

        // Check for invalid url
        if (empty($url)) {
            return $url;
        }

        // Prepend the CDN url
        if (strpos($url, '//') !== FALSE) {
            // we have a URI, don't modify it
            return $url;
        } else if (strpos('/', $url) !== 0) {
            // we have a url not coming from the root, fix it
            return $pull_url . $request->path() . $url;
        } else {
            // it should be safe to concatenate the url
            return $pull_url . $url;
        }
    }

    /**
     * CDN Enabled
     *
     * Checks whether or not we should be using the CDN
     *
     * @return boolean  True if production or debugging, false if not
     */
    private static function shouldUseCDN()
    {
        if (! Config::get('laravel5-cdn-views.enabled')) {
            return false;
        }

        $request = App::make('request');

        if($request->secure() && ! Config::get('laravel5-cdn-views.ssl_enabled')) {
            return false;
        }

        $disabled_routes = Config::get('laravel5-cdn-views.disabled_routes');
        foreach($disabled_routes as $route) {
            if($request->is($route)) {
                return false;
            }
        }

        if (App::environment('production') ||  Config::get('laravel5-cdn-views.debug')) {
            return true;
        }

        return false;
    }
}
