<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;


abstract class Component extends \JControllerBase{

    protected $_controllers = array();

    /**
     * @param $controllerClassName
     *
     * @return mixed
     *
     * @since version
     */
    public function getController($controllerClassName)
    {
        /*
         * Let's restrict double creation throw out app since we have no single tone
         */
        if (!isset($this->_controllers[$controllerClassName]))
        {
            $this->_controllers[$controllerClassName] = new $controllerClassName();
        }

        return $this->_controllers[$controllerClassName];
    }

}