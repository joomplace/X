<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Renderer;

use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomplace\X\View;

trait Api
{
    function render($tpl = null)
    {
        /** @var View $this */
        return new JsonResponse($this->getProperties(), Factory::getApplication()->getMessageQueue(true));
    }

}