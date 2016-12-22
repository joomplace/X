<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

use JApplicationBase;
use JInput;

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
	protected static $_default_controller = 'dashboard';
	protected static $_default_task = 'index';
	/** @var array $_components Cache of JooYii Components instances */
	private static $_components = array();
	/** @var array $_controllers Cache of (sub)controllers instances */
	protected $_controllers = array();
	/** @var string $_namespace Automatically changed when class extended */
	protected $_namespace = __NAMESPACE__;

	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);
		$this->setNamespace();
	}

	abstract protected function setNamespace();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function getDefaultController()
	{
		return static::$_default_controller;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function getDefaultTask()
	{
		return static::$_default_task;
	}

	/**
	 * @not_used
	 *
	 * @param string $component
	 *
	 * @return Component Component instance
	 *
	 * @since 1.0
	 */
	public static function getInstance($component)
	{
		if (!isset(self::$_components[$component]))
		{
			self::$_components[$component] = new $component();
		}

		return self::$_components[$component];
	}

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
		try
		{
			$this->preExecution();

			$gconfig = \JFactory::getConfig();
			$input   = $this->getInput();
			$cconfig = \JComponentHelper::getParams($input->get('option'));

			$json_input    = json_decode(file_get_contents('php://input'));
			$json_registry = new \Joomla\Registry\Registry($json_input);

			$controller = $input->getString('controller', static::$_default_controller);
			$task       = $input->getString('task', $json_registry->get('task', ''));
			if(!$task){
				$task = static::$_default_task;
			}
			$task = explode('.', $task);
			$action = $task[0];
			$input->set('view', $input->getString('view', $controller));

			$controllerClass = $this->getController($this->_namespace . '\\Controller\\' . $controller);

			if(Helper::methodExists($controllerClass, 'preInitialize')){
				Helper::callBindedFunction($controllerClass, 'preInitialize', array($json_registry, $input, $cconfig, $gconfig));
			}
			Helper::callBindedFunction($controllerClass, $action, array($json_registry, $input, $cconfig, $gconfig));

		}
		catch (\Exception $e)
		{
			\JLog::add($e->getMessage(), \JLog::ERROR, 'jerror');
		}
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
