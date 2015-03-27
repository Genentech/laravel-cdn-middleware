<?php
namespace Genentech\CdnViews\Views;

use Illuminate\View\Factory as Factory;

class CdnViewFactory extends Factory
{

    public function make($view, $data = array(), $mergeData = array())
    {
        if (isset($this->aliases[$view])) {
            $view = $this->aliases[$view];
        }

        $view = $this->normalizeName($view);

        $path = $this->finder->find($view);

        $data = array_merge($mergeData, $this->parseData($data));

        $this->callCreator($view = new CdnView($this, $this->getEngineFromPath($path), $view, $path, $data));

        return $view;
    }

}
