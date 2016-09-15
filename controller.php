<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;


class Controller
{
	protected $_classreflection;
	protected $_model;
	protected $_models = array();

	protected function getClassParentNameSpacing(){
		return substr($this->_classreflection->getNamespaceName(), 0, strrpos($this->_classreflection->getNamespaceName(), '\\'));
	}

	public function __construct()
	{
		$this->setModel($this->getClassName());
	}

	protected function getClassName(){
		if(!$this->_classreflection){
			$this->_classreflection = new \ReflectionClass($this);
		}
		return $this->_classreflection->getShortName();
	}


	/**
	 * @param null $modelname
	 * @param bool $force_new
	 *
	 * @return Model
	 *
	 * @since version
	 */
	public function getModel($modelname = null, $force_new = false)
	{
		if(is_null($modelname)){
			$modelname = $this->_model;
		}
		if(!isset($this->_models[$modelname]) || $force_new){
			$modelClass = $this->getClassParentNameSpacing().'\\Model\\'.$modelname;
			$this->_models[$modelname] = new $modelClass();
		}
		return $this->_models[$modelname];
	}

	/**
	 * @param string $view
	 *
	 * @return View
	 *
	 * @since version
	 */
	private function getView($view = null){
		if(is_null($view)){
			$view = $this->getClassName();
		}
//		$viewClass = $this->getClassParentNameSpacing().'\\View\\'.$view;
//		$view = new $viewClass();
        $view = new View($view);
		return $view;
	}

	/**
	 * @param string $model
	 *
	 * @since version
	 */
	public function setModel($model)
	{
		$this->_model = $model;
	}

	public final function model($task,Array $jform){
	    $jform = \JFactory::getApplication()->input->get('jform',array(),'array');
        $model = $this->getModel();
        $model->$task($jform);
    }

	protected function render($view, $vars){
		$view = $this->getView($view);
        $view->setNamespace($this->getClassParentNameSpacing());
		foreach ($vars as $var => $value){
			$view->setVar($var,$value);
		}
		return $view->render();
	}

	public function index($limit = false, $limitstart = 0){
		$output = $this->render('index', array());
        echo $output;
	}

	public function proxy($task){
	    $task = explode('.', $task);
        unset($task[0]);
        $task = array_values($task);
        /*
         * TODO: think on further extension
         */
        switch ($task[0]){
            case 'model':
                $model = $this->getModel();
                if(Helper::callBindedFunction($model,$task[1])!==false){
                    Helper::callBindedFunction($this,'index');
                }else{
                    echo 'smth went wrong';
                }
        }
    }

}