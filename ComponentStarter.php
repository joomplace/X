<?php
/**
 * Copyright (c) 2018. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 03.02.2018
 * Time: 12:22
 */

namespace Joomplace\X;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

abstract class ComponentStarter
{
    /**
     * @param string        $dispatcher         Component dispatcher class name
     * @param string|null   $defaultController  Name of the default controller to run
     * @param string|null   $migrationsPath     Path to migrations folder to run if any
     *
     * @since 1.0
     */
    public static function startup(string $dispatcher, $defaultController = null, $migrationsPath = null){

        Factory::getDocument()->setMetaData('api-token',Session::getFormToken());

        $input = Factory::getApplication()->input;
        $jsonInput = json_decode(file_get_contents('php://input'));
        if($jsonInput){
            foreach ($jsonInput as $k => $v){
                $input->def($k,$v);
            }
        }

        $task = $input->get('task');
        if(strpos($task,'.')!==false){
            $task = explode('.',$task);
            $controller = array_shift($task);
            $task = implode('.',$task);
            $input->set('task',$task);
            $input->def('controller',$controller);
        }

        if($migrationsPath){
            $dispatcher::migrate($migrationsPath);
        }
        echo $dispatcher::getInstance($input->get('controller',$defaultController))->execute($input->get('task'));
    }
}