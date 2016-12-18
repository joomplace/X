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
		if(!self::$option){
			$scope = Loader::getClassScope(Helper::getClassName(new static(),false));
			self::$option = 'com_'.lcfirst($scope);
		}
		$nsp = Helper::getClassParentNameSpacing(new static());
		list($path) = Loader::extractPaths($nsp . '\\Controller\\', '/');
		$controllers = \JFolder::files($path);
		foreach ($controllers as $controller){
			$controller = str_replace('.php','',$controller);
			if(!in_array($controller,self::$ignore_controllers)){
				self::addEntry($controller,$view==$controller);
			}
			$ctrlClass = $nsp.'\\Controller\\'.$controller;
			$ctrl = new $ctrlClass();
			/** @var Model $model */
			$model = $ctrl->getModel();
			if($model){
				/*
				 * TODO: May be Admin/Site part would need to be removed (depending on custom fields arch)
				 */
				$customfieldsClass = '\\Joomplace\\Customfields\\'.(\JFactory::getApplication()->isAdmin()?'Admin':'Site').'\\Model\\CustomField';
				if (class_exists($customfieldsClass))
				{
					$data = array('option'=>'com_customfields','context'=>$model->getContext(),'extension'=>self::$option);
					\JFactory::getLanguage()->addString(strtoupper(implode('_',$data)),ucfirst($controller).' customfields');
					self::addCustomEntry($data);
				}
			}
		}
		return true;
	}

	protected static function addEntry($controller, $active = false){
		\JHtmlSidebar::addEntry(
			\JText::_(strtoupper(self::$option.'_'.$controller)),
			\JRoute::_('index.php?option='.self::$option.'&controller='.$controller),
			$active
		);
	}

	protected static function addCustomEntry($array = array(), $active = false){
		$path = array();
		$array['extension'] = self::$option;
		foreach ($array as $key => $val){
			$path[] = "$key=$val";
		}
		\JHtmlSidebar::addEntry(
			\JText::_(strtoupper(implode("_",$array))),
			\JRoute::_('index.php?'.implode("&",$path)),
			$active
		);
	}

}