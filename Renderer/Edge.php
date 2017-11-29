<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Renderer;

use Windwalker\Edge\Edge as EdgeEngine;
use Windwalker\Edge\Loader\EdgeFileLoader;

trait Edge
{
    function render($tpl = null)
    {
        $fileLoader = new EdgeFileLoader($this->_path['template']);
        $renderer = new EdgeEngine($fileLoader);
        return $renderer->render($tpl ? ($this->getLayout() . '_' . $tpl) : $this->getLayout(), $this->getProperties());
    }

}