<?php
namespace Genentech\CdnViews\Views;

use Closure;
use Illuminate\View\View;

class CdnView extends View
{

    public function render(Closure $callback = null)
    {
        $contents = parent::render($callback);

        // only CDNify assets if on final pass of rendering
        if ($this->factory->doneRendering()) {
            $contents = CdnHelper::convertContentForCDN($contents);
        }

        return $contents;
    }
}
