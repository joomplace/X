<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

/**
 * Main (if somehow not single) component entry abstraction
 * Fully preimplemented
 * One reason why need to be extended and implemented is
 * namespace automatically changed and autoloaded
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
abstract class Component extends \JControllerBase
{

	/** @var array $_controllers Cache of (sub)controllers instances */
	protected $_controllers = array();
	protected $_default_controller = 'dashboard';
	protected $_default_task = 'index';
	/** @var string $_namespace Automatically changed when class extended */
	protected $_namespace = __NAMESPACE__;

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   1.0
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute()
	{

		$this->preExecution();

		$gconfig = \JFactory::getConfig();
		$input   = $this->getInput();
		$cconfig = \JComponentHelper::getParams($input->get('option'));

		$json_input    = json_decode(file_get_contents('php://input'));
		$json_registry = new \Joomla\Registry\Registry();
		if ($json_input)
		{
			foreach ($json_input as $key => $value)
			{
				$json_registry->set($key, $value);
			}
		}

		$controller = $input->getString('controller', $this->_default_controller);
		$task       = explode('.', $input->getString('task', $json_registry->get('task', $this->_default_task)));
		$action     = $task[0];
		$input->set('view', $input->getString('view', $controller));

		$controllerClass = $this->getController($this->_namespace . '\\Controller\\' . $controller);

		\Joomplace\Library\JooYii\Helper::callBindedFunction($controllerClass, $action, array($input, $json_registry, $cconfig, $gconfig));

		return true;
	}

	/**
	 * Method to allow checks and other pre execution things
	 *
	 * @since 1.0
	 */
	protected function preExecution()
	{
		// pre execution things if needed
	}

	/**
	 * Get controller(s) instance
	 *
	 * @param $controllerClassName
	 *
	 * @return mixed
	 *
	 * @since 1.0
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