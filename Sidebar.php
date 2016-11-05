<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 02.11.2016
 * Time: 7:57
 */

namespace Joomplace\Library\JooYii;


abstract class Sidebar
{
	protected static $option;
	protected static $ignore_controllers = array();

	/**
	 * @param \Joomplace\Library\JooYii\View $view Current view
	 *
	 *
	 * @since 1.1
	 */
	abstract public static function setUp($view);

	public static function setControllersEntries($view = 'Dashboard', $layout = 'default'){
		jimport('joomla.filesystem.folder');
		if(!static::$option){
			$scope = Loader::getClassScope(Helper::getClassName(new static(),false));
			static::$option = 'com_'.lcfirst($scope);
		}
		$nsp = Helper::getClassParentNameSpacing(new static());
		list($path) = Loader::extractPaths($nsp . '\\Controller\\', '/');
		$controllers = \JFolder::files($path);
		foreach ($controllers as $controller){
			$controller = str_replace('.php','',$controller);
			if(!in_array($controller,static::$ignore_controllers)){
				static::addEntry($controller,$view==$controller);
			}
		}
		return true;
	}

	protected static function addEntry($controller, $active = false){
		\JHtmlSidebar::addEntry(
			\JText::_(strtoupper(static::$option.'_'.$controller)),
			\JRoute::_('index.php?option='.static::$option.'&controller='.$controller),
			$active
		);
	}

}