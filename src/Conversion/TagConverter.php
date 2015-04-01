<?php
namespace Genentech\CdnViews\Conversion;

use Closure;
use DOMNode;
use Genentech\CdnViews\Exceptions\TagNotRegisteredException;

/**
 * Class TagConverter
 *
 * Applies a Callback to a given html tag
 */
class TagConverter
{
    protected $callbacks = [];

    public function registerTag($tag, Closure $callback)
    {
        if (! $this->hasTag($tag)) {
            $this->callbacks[$tag] = $callback;
        } else {
            $inner_callback = $this->callbacks[$tag];

            $this->callbacks[$tag] = function (DOMNode $node) use ($callback, $inner_callback) {
                return $callback($inner_callback($node));
            };
        }
    }

    public function unregisterTag($tag)
    {
        if ($this->hasTag($tag)) {
            unset($this->callbacks[$tag]);
        }
    }

    public function convertNode(DOMNode $node)
    {
        if (! $this->hasTag($node->tagName)) {
            throw new TagNotRegisteredException("Unregistered Tag " . $node->tagName . " passed to TagConverter");
        }

        return $this->callbacks[$node->tagName]($node);
    }

    protected function hasTag($tag)
    {
        return isset($this->callbacks[$tag]);
    }
}
