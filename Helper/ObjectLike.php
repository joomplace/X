<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Helper;


trait ObjectLike
{
    function getProperties($public = true)
    {
        $vars = get_object_vars($this);

        if ($public) {
            foreach ($vars as $key => $value) {
                if ('_' == substr($key, 0, 1)) {
                    unset($vars[$key]);
                }
            }
        }

        return $vars;
    }
}
