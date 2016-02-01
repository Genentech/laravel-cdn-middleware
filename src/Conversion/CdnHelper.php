<?php
namespace Genentech\CdnViews\Conversion;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Masterminds\HTML5;
use DOMNode;

/**
 *  CDN Helper
 *
 *  Collection of methods to assist with the transformation of URLs into their CDN counterparts
 */
class CdnHelper
{
    protected $cdnUrl;
    protected $valid_tags = [];
    protected $tagConverter;

    public function __construct($cdnUrl, $valid_tags)
    {
        $this->cdnUrl = $cdnUrl;
        $this->valid_tags = $valid_tags;
        $this->tagConverter = new TagConverter();

        $this->registerTags();
    }

    /**
     * Register all provided tags with the tag converter
     */
    private function registerTags()
    {
        foreach ($this->valid_tags as $tag) {
            $this->tagConverter->registerTag($tag, function (DOMNode $element) {
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
     * This function converts content to be served by the CDN.
     * It parses through the DOM for any of the set targets
     * and replaces their src and href with the correct versions.
     * Because it adds any missing head or body tags it should only
     * be run only on a whole page.
     *
     * @param  string $content The original content
     * @return string          The content via CDN
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
     * Converts a URL to be served via the CDN
     *
     * @param  string $url The URL to be converted
     * @return string      The URL with the correct CDN URL prepended
     */
    public function convertURL($url)
    {
        return $this->prependCDN($url, $this->cdnUrl);
    }

    /**
     * Prepend CDN
     *
     * Turns a regular url into a CDN-prepended url
     *
     * @param  string $url       The URL to be converted
     * @param  string $pull_url  The URL to be prepended
     * @return string            The URL with the cdn prepended
     */
    public static function prependCDN($url, $pull_url)
    {
        // Check for invalid url
        if (empty($url)) {
            return $url;
        }

        // Prepend the CDN url
        if (strpos($url, '//') !== false) {
            // URI; don't modify it
            return $url;
        } else if (strpos($url, '/') !== 0) {
            // TODO: we have a url not coming from the root, we'll need to fix it using the request
            Log::warning('Non root relative URL ' . $url . ' passed to CDN helper');

            // just return for now
            return $url;
        } else {
            // it should be safe to concatenate the url
            return $pull_url . $url;
        }
    }
}
