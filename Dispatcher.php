<?php

namespace JoomPlaceX;

use JInput;
use Joomla\Application\AbstractApplication;

class Dispatcher extends \JControllerBase
{
    protected $default_view = 'dashboard';
    protected $namespace = null;

    public function __construct(
        JInput $input = null,
        AbstractApplication $app = null
    ) {
        parent::__construct($input, $app);
        if(!$this->namespace){
            $this->namespace = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
        }
    }


    public function execute($task = null)
    {
        $this->dispatch($task);
    }

    public function dispatch($task = null)
    {
        if(\JFactory::getApplication()->isClient('administrator')){
            $this->addMustHaveButtons();
        }

        $this->input->def('view',$this->default_view);

        if(strpos($task,'.')){
            list($controllerName, $action) = explode('.', $task);
        }else{
            $controllerName = $this->input->get('view');
            $action = trim($task, '.');
        }
        $controllerName = $this->namespace.'\\Controller\\'.ucfirst($controllerName);
        $controller = new $controllerName;
        $controller->execute($action);
    }

    protected function addMustHaveButtons()
    {
        if(\JText::_('BRAND_LOGO')){
            \JToolbarHelper::custom('dashboard.brand','hide','version',\JText::_('BRAND_LOGO'), false);
            \JFactory::getDocument()->addStyleDeclaration('#toolbar #toolbar-hide{float:right;} #toolbar #toolbar-hide .btn > * {max-height: 2.1em;} #toolbar #toolbar-hide .icon-hide{display:none;}');
//            \JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function($){$("#toolbar #toolbar-hide .btn").on("click",function(e){e.preventDefault();return false;})});');
        }

        $xml = simplexml_load_file(JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$this->input->get('option').DIRECTORY_SEPARATOR.str_replace('com_','',$this->input->get('option')).'.xml');
        \JToolbarHelper::custom('dashboard.version','info','version',(string)$xml->version, false);
        \JFactory::getDocument()->addStyleDeclaration('#toolbar #toolbar-info{float:right;}');

        \JToolbarHelper::preferences($this->input->get('option'));
    }
}