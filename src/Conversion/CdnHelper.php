<?php
namespace Genentech\CdnViews\Conversion;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
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
    protected $request;
    protected $valid_tags = [];
    protected $disabled_routes = [];
    protected $enabled_for_ssl;

    function __construct($request, $cdnUrl, $valid_tags, $enabled_for_ssl = true)
    {
        $this->request = $request;
        $this->cdnUrl = $cdnUrl;
        $this->valid_tags = $valid_tags;
        $this->enabled_for_ssl = $enabled_for_ssl;
        $this->tagConverter = new TagConverter();
        $this->registerTags();
    }

    private function registerTags() {
        foreach($this->valid_tags as $tag) {
            $this->tagConverter->registerTag($tag, function(DOMNode $element) {
                if ($element->hasAttribute("src")) {
                    $element->setAttribute("src",
                        $this->convertURL($element->getAttribute("src"))
                    );
                }
                if ($element->hasAttribute("href")) {
                    $element->setAttribute("href",
                        $this->convertURL($element->getAttribute("href"))
                    );
                }
                return $element;
            });
        }
    }

    /**
     * Convert Page For CDN
     *
     * This function converts content to be served by the CDN
     * It parses through the DOM for any of the set targets
     * and replaces their src and href with the correct versions
     * Because it adds any missing head or body tags it should only
     * be run only on a whole page
     *
     * @param  string $content The original content
     * @return string            The content via CDN
     */
    public function convertPageForCDN($content)
    {
        $html5 = new HTML5();
        $doc = $html5->loadHTML($content);

        foreach ($this->valid_tags as $target) {
            $nodes = $doc->getElementsByTagName($target);
            foreach ($nodes as $iterator => $element) {
                $converted = $this->tagConverter->convertNode($element);
                $element->parentNode->replaceChild($converted, $element);
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
            return $this->prependCDN($url, $this->cdnUrl);
        } else {
            return $url;
        }
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
    public static function prependCDN($url, $pull_url)
    {

        //$request = App::make('request');

        // Check for invalid url
        if (empty($url)) {
            return $url;
        }

        // Prepend the CDN url
        if (strpos($url, '//') !== FALSE) {
            // we have a URI, don't modify it
            return $url;
        } else if (strpos($url, '/') !== 0) {
            Log::warning('Non root relative URL'. $url . 'passed to CDN helper');
            // TODO: we have a url not coming from the root, we'll need to fix it using the request
            // just return for now
            return $url;
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
     * @return boolean
     */
    private function shouldUseCDN()
    {
        $request = $this->request;
        if($request->secure() && ! $this->enabled_for_ssl) {
            return false;
        }

        foreach($this->disabled_routes as $route) {
            if($request->is($route)) {
                return false;
            }
        }

        return true;
    }

    public function blacklistRoute($route) {
        $this->disabled_routes[] = $route;
    }
}
