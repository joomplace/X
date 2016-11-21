<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Fields
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii\Fields;

use Joomplace\Library\JooYii\Helper;
use Joomplace\Library\JooYii\Loader;

jimport('joomla.form.helper');
\JFormHelper::loadFieldClass('list');
/**
 * Dynamic list field type
 *
 * @package     Joomplace\Library\JooYii\Fields
 *
 * @since       1.0
 */
class DDUpload extends \JFormField
{
	/**
	 * Fully qualified class name
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $type = '\\Joomplace\\Library\\JooYii\\Fields\\DDUpload';
	/** @var array $_options Options store */
	protected $_files = array();

	protected function getFiles()
	{

	}

	public function getInput(){
		$option = \JFactory::getApplication()->input->get('option');
		$this->getFiles();
		list($def_path) = Loader::getPathByPsr4('Joomplace\\Library\\JooYii\\Layouts\\', '/');
		$params = array();
		foreach ($this as $k => $item){
			if(!is_array($item)){
				$params[$k] = $item;
			}
		}
		$params['ajax_url'] = 'index.php?option='.$option.'&path='.$this->getAttribute('path','').'&task=proxy.field&field_type='.$this->type.'&'. \JSession::getFormToken() .'=1';
		$html = \JLayoutHelper::render('form.ddupload', $params, $def_path);
		return $html;
	}

	public static function process(){
		if(\JSession::checkToken('get')){
			list($def_path) = Loader::getPathByPsr4('Joomplace\\Library\\JooYii\\Layouts\\', '/');
			$params = array();
			$file = \JFactory::getApplication()->input->files->get('file',array(),'ARRAY');
			$params['file_name'] = $file['name'];
			if($file && $file = Helper::uploadFile($file, 'tmp/'.\JFactory::getApplication()->input->get('path',null,'PATH'))){
				$params['file'] = $file;
				echo \JLayoutHelper::render('form.ddupload_done', $params, $def_path);
				\JFactory::getApplication()->close(200);
			}else{
				echo \JLayoutHelper::render('form.ddupload_failed', $params, $def_path);
				\JFactory::getApplication()->close(500);
			}
		}
		\JFactory::getApplication()->close(403);
	}

	public static function onBeforeStore(&$model, $name, $defenition){
		$path = $defenition['path'];
		$model->$name = explode('|',$model->$name);
		$model->$name = array_map(function($item) use ($path){
			if(strpos($item,'tmp/')==1){
				$currentPath = JPATH_SITE.$item;
				$relPath = str_replace('tmp/','',$item);
				$targetPath = JPATH_SITE.$relPath;
				if(Helper::moveFile($currentPath, $targetPath)){
					return $relPath;
				}
			}
			return $item;
		},$model->$name);
		$model->$name = json_encode($model->$name);
		echo "<pre>";
		print_r($model);
		echo "</pre>";
		die('');
		/*
		 * Move files from tmp to normal directory
		 */
		return true;
	}

	public static function onAfterStore(&$model, $name, $defenition){
		/*
		 * Delete unseted files
		 */
		return true;
	}
}