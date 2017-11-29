<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Renderer;


trait PlainPHP
{
    function render($tpl = null)
    {
        $result = $this->loadTemplate($tpl);
        return $result;
    }

}