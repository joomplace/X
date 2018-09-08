<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;


use Joomla\CMS\Error\Renderer\JsonRenderer;

class ControllerAPI extends Controller
{
    public function execute($task)
    {
        $this->app->setHeader('Content-Type', 'application/json');
        try {
            http_response_code(200);
            parent::execute($task);
        } catch (\Exception $error) {
            http_response_code(500);
            echo (new JsonRenderer())->render($error);
        }
        $this->app->close();
    }
}