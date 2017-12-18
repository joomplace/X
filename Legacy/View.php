<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Legacy;

use \Joomla\CMS\MVC\View\HtmlView;
use Joomplace\X\Helper\Object;
use \Joomplace\X\Renderer\PlainPHP as JoomlaEngine;
use \Joomplace\X\Renderer\Edge as EdgeEngine;

class View extends HtmlView
{
    use Object;
    use EdgeEngine;

    public function __construct(array $config = array())
    {
        if (isset($config['name'])) {
            list($config['name'], $config['layout']) = explode(':', $config['name']);
        }
        if (!isset($config['base_path'])) {
            $config['base_path'] = str_replace('//', '/', $this->getExecutedClassDirictory() . '/../');
            $config['template_path'] = $config['base_path'].'tmpl/'.strtolower($config['name']);
        }
        parent::__construct($config);
    }

    public function getExecutedClassDirictory()
    {
        $reflector = new \ReflectionClass(get_class($this));
        $fn = $reflector->getFileName();
        return dirname($fn);
    }

    public function display($tpl = null)
    {
        $result = $this->render($tpl);

        echo $result;
    }
}