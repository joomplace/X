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

    protected function returnItem($item)
    {
        $this->item = $item;
        return new JsonResponse($this->getProperties(), Factory::getApplication()->getMessageQueue(true));
    }

    public function stored($item){
        return $this->returnItem($item);
    }

    public function updated($item){
        return $this->returnItem($item);
    }

    public function destroyed($item){
        return $this->returnItem($item);
    }

}