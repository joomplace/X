<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 18.12.2017
 * Time: 17:41
 */

namespace Joomplace\X;


class Dispatcher extends \Joomla\CMS\Dispatcher\Dispatcher
{
    use Migrations;

    public function dispatch()
    {
        $_config = new Joomplace\Component\Profiler\Site\Config();
        $this->input->def('controller', $_config->defaultController);
        $this->input->def('task', $_config->defaultView);
        parent::dispatch();
    }

    public function getController(string $name, string $client = '', array $config = array()): \Joomla\CMS\MVC\Controller\BaseController
    {
        // Set up the namespace
        $namespace = rtrim($this->namespace, '\\') . '\\';

        // Set up the client
        $client = $client ?: ucfirst($this->app->getName());

        $controllerClass = $namespace . $client . '\\Controller'.
            (($format = $this->app->input->get('format','web'))=='web'?'':'\\'.$format).
            '\\' .ucfirst($name);

        if (!class_exists($controllerClass))
        {
            $controllerClass = $namespace . $client . '\\Controller'.'\\' .ucfirst($name);
            if (!class_exists($controllerClass))
            {
                throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $controllerClass));
            }
        }

        $config['namespace'] = $namespace;

        return new $controllerClass($config, new \Joomla\CMS\MVC\Factory\MVCFactory($namespace, $this->app), $this->app, $this->input);
    }

}