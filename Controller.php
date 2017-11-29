<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;


use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;


class Controller extends BaseController
{
    protected $_namespace = null;

    public function __construct(
        array $config = array(),
        MVCFactoryInterface $factory = null,
        $app = null,
        $input = null
    ) {
        $this->_namespace = $config['namespace'];
        parent::__construct($config, $factory, $app, $input);
    }

    public function execute($task)
    {
        $response = parent::execute($task);
        echo $response;
    }

    public function index($cachable = false, $urlparams = array())
    {
        $view = $this->getView();
        return $view->render();
    }

    protected function createView($name, $prefix = '', $type = '', $config = array())
    {
        if (!isset($config['base_path'])) {
            $config['base_path'] = $this->basePath;
        }
        $document = \JFactory::getDocument();
        $type = $document->getType();

        $viewClass = $this->_namespace . ucfirst($this->app->getName()) . '\\View' .
            ($prefix ? '\\' . ucfirst($prefix) : '') . ($type == 'html' ? '' : '\\' . ucfirst($type)) .
            '\\' . ucfirst($name);
        $view = new $viewClass(['name' => $this->name . ':' . $this->input->get('layout', $this->task, 'cmd')]);
        return $view;
    }

}