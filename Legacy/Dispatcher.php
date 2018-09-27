<?php
/**
 * Copyright (c) 2018. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 18.12.2017
 * Time: 17:41
 */

namespace Joomplace\X\Legacy;


use Joomla\CMS\Factory;
use Joomplace\X\Helper\Migrations;

class Dispatcher
{
    use Migrations;

    public static function getInstance($prefix, $config = array())
    {
        // Set up the namespace
        $namespace = rtrim(static::$namespace, '\\') . '\\';

        // Set up the client
        $client = ucfirst(Factory::getApplication()->getName());
        if($client == 'Administrator'){
            $client = 'Admin';
        }

        $controllerClass = $namespace . $client . '\\Controller'.
            (($format = Factory::getApplication()->input->get('format','web'))=='web'?'':'\\'.$format).
            '\\' .ucfirst($prefix);

        if (!class_exists($controllerClass))
        {
            $controllerClass = $namespace . $client . '\\Controller'.'\\' .ucfirst($prefix);
            if (!class_exists($controllerClass))
            {
                throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $controllerClass));
            }
        }

        $config['namespace'] = $namespace;

        return new $controllerClass($config);
    }
}