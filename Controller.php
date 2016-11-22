<?php
/**
 * @package     Joomplace\Library\JooYii
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

jimport( 'joomla.filesystem.file' );
/**
 * Controller class for implementing C letter of new Joomla!CMS MVC
 * (JooYii MVC)
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
class Controller
{
	protected $_classreflection;
	protected $_model;
	protected $_models = array();
	private $_storage = array();

	public function setStorage($storage, $value)
	{
		$this->_storage[$storage] = $value;
	}

	public function getStorage($storage = null)
	{
		$data = new \Joomla\Registry\Registry($this->_storage);
		if(is_null($storage)){
			return $data;
		}
		return $data->get($storage,null);
	}

	/**
	 * Controller constructor
	 *
	 * @since 1.0
	 */
	public function __construct()
	{
		if (!class_exists('JToolbarHelper'))
		{
			jimport('includes.toolbar', JPATH_ADMINISTRATOR);
		}
		$this->setModel($this->getClassName());
	}

	/**
	 * Alias to JooYii Helper
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function getClassName()
	{
		return Helper::getClassName($this);
	}

	/**
	 * Method to get helper class
	 *
	 * @param null $helpername
	 * @param bool $force_new
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public function getHelper($helpername = null, $force_new = true)
	{
		$helperClass = $this->getClassParentNameSpacing() . '\\Helper\\' . $helpername;
		$helper      = new $helperClass();

		return $helper;
	}

	/**
	 * Alias to JooYii Helper
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function getClassParentNameSpacing()
	{
		return Helper::getClassParentNameSpacing($this);
	}

	/**
	 * Proxy to call Model (default for controller) method and
	 * pass array of data
	 *
	 * @param string $task  Method to call
	 * @param array  $jform Array of data to pass
	 *
	 *
	 * @since 1.0
	 */
	public final function model($task, Array $jform)
	{
		$model = $this->getModel();
		$model->$task($jform);
	}

	/**
	 * Get components model
	 *
	 * @param null|string $modelname Model to load
	 * @param bool        $force_new Flag to force new
	 *
	 * @return Model
	 *
	 * @since 1.0
	 */
	public function getModel($modelname = null, $force_new = false)
	{
		if (is_null($modelname))
		{
			$modelname = $this->_model;
		}
		if (!isset($this->_models[$modelname]) || $force_new)
		{
			$modelClass = $this->getClassParentNameSpacing() . '\\Model\\' . $modelname;
			if (class_exists($modelClass))
			{
				$this->_models[$modelname] = new $modelClass();
			}
			else
			{
				$this->_models[$modelname] = false;
			}
		}

		return $this->_models[$modelname];
	}

	/**
	 * @param string $model
	 *
	 * @since 1.0
	 */
	public function setModel($model)
	{
		$this->_model = $model;
	}

	/**
	 * Method to proxy task to somewhere else
	 *
	 * @param string $task Task to process
	 *
	 *
	 * @since 1.0
	 */
	public function proxy($task)
	{
		$task = explode('.', $task);
		unset($task[0]);
		$task = array_values($task);
		/*
		 * TODO: think on further extension
		 */
		switch ($task[0])
		{
			case 'model':
				$model = $this->getModel();
				if (Helper::callBindedFunction($model, $task[1]) !== false)
				{
					Helper::callBindedFunction($this, 'index');
				}
				else
				{
					echo 'smth went wrong';
				}
				break;
			case 'field':
				$field = \JFactory::getApplication()->input->get('field_type','','safe_html');
				$field_method = \JFactory::getApplication()->input->get('field_method','process','string');
				if($field && method_exists($field,$field_method)){
					Helper::callBindedFunction($field,$field_method);
				}
				break;
		}
		die('exit'); // we never must go this far...
		/**
		 * but we get here on delete at least... need to redirect model case?
		 */
	}

	/**
	 * Alias for edit(0)
	 *
	 * @since 1.0
	 */
	public function add($return_url = '')
	{
		$this->edit(array(),$return_url);
	}

	/**
	 * Default method to edit record of default model
	 *
	 * @param array $cid Array type used for J compatibility only
	 *
	 *
	 * @since 1.0
	 */
	public function edit(array $cid = array(), $return_url = '')
	{
		$model = $this->getModel(null, true);
		if ($cid)
		{
			$model->load($cid[0]);
			\JToolbarHelper::title(\JText::_(strtoupper($this->getClassName()) . '_EDIT_TITLE'), 'pencil');
		}
		else
		{
			\JToolbarHelper::title(\JText::_(strtoupper($this->getClassName()) . '_NEW_TITLE'), 'pencil-2');
		}

		$vars = array(
			'item' => $model,
			'return_url' => $return_url,
		);

		$key = $model->getKeyName();
		\JToolbarHelper::apply('apply');
		\JToolbarHelper::save('save');
		if ($model->$key)
		{
			\JToolbarHelper::save2copy('save2copy');
		}
		\JToolbarHelper::save2new('save2new');
		\JToolbarHelper::cancel('cancel');

		echo $this->render($this->getClassName().'.edit', $vars);
	}

	/**
	 * Method to render Html markup of passed View
	 *
	 * @param string $view View name or view.layout
	 * @param array  $vars Variables
	 *
	 * @return string Html markup
	 *
	 * @since 1.0
	 */
	protected function render($viewname, $vars)
	{
		$view = $this->getView($viewname);
		$view->setNamespace($this->getClassParentNameSpacing());
		foreach ($vars as $var => $value)
		{
			$view->setVar($var, $value);
		}
		$this->preRender($viewname, $view->getLayout(),$vars);

		return $view->render('',$vars);
	}

	/**
	 * Get view object
	 *
	 * @param string $view View class name
	 *
	 * @return View View object
	 *
	 * @since 1.0
	 */
	private function getView($view = null)
	{
		if (is_null($view))
		{
			$view = $this->getClassName();
		}
//		$viewClass = $this->getClassParentNameSpacing().'\\View\\'.$view;
//		$view = new $viewClass();
		$view = new View($view);

		return $view;
	}

	/**
	 * Alias for edit & add
	 *
	 * @param array $jform Form data
	 *
	 *
	 * @since 1.0
	 */
	public function saveandnew(array $jform)
	{
		$this->apply($jform, true);
	}

	/**
	 * Apply changes to entry
	 *
	 * @param array $jform Form data
	 * @param bool  $tonew Is new form should be rendered
	 *
	 *
	 * @since 1.0
	 */
	public function apply(array $jform, $tonew = false, $return_url = '')
	{
		$model = $this->getModel();
		$form = $model->getForm();
		$this->deletePrevPhotos($model, $jform);
//		$jform = $form->filter($jform);
		/** @var \Joomla\Registry\Registry $data */
		$form->bind($jform);
		/* recursive jform */
		$rjform = $form->getData()->toArray();
		$return = $form->validate($rjform);
		if (!$return)
		{
			array_map(function($e){
				\JFactory::getApplication()->enqueueMessage($e->getMessage(),'error');
			},$form->getErrors());
			$tonew = false;
		}else{
			if(!$model->save($jform)){
				$tonew = false;
			}
		}
		if (!$tonew)
		{
			$key = $model->getKeyName();
			$this->edit(array($model->$key), $return_url);
		}
		else
		{
			$this->edit(array(), $return_url);
		}
	}

	/**
	 * Alias to save item & changes to a new entry
	 *
	 * @param array $jform Form data
	 *
	 *
	 * @since 1.0
	 */
	public function save2copy(array $jform)
	{
		$jform['id'] = '';
		$this->apply($jform);
	}

	/**
	 * Alias to save item and go to a new entry
	 *
	 * @param array $jform Form data
	 *
	 *
	 * @since 1.0
	 */
	public function save2new(array $jform)
	{
		$this->apply($jform, true);
	}

	/**
	 * Default method to save
	 *
	 * @param array $jform Form data
	 *
	 *
	 * @since 1.0
	 */
	public function save(array $jform, $return_url = '')
	{
		$model = $this->getModel();
		$this->deletePrevPhotos($model, $jform);
		$model->save($jform);
		$this->cancel($return_url);
	}

	/**
	 * Default list render and echo action
	 *
	 * @param bool        $limit      Limit of records
	 * @param int         $limitstart Offset
	 * @param bool|string $view       View name
	 *
	 *
	 * @since 1.0
	 */
	public function index($limit = false, $limitstart = 0, $view = false)
	{
		$model = $this->getModel();
		$vars  = array();
		if ($model)
		{
			\JToolbarHelper::addNew('add');
			$state = $model->getState();
			if ($limit !== false)
			{
				$items = $model->getList($limitstart, $limit, $this->getStorage('conditions'));
			}
			else
			{
				$items = $model->getList();
			}
			$pagination = $model->getPagination();
			$vars       = array(
				'state'      => $state,
				'items'      => $items,
				'pagination' => $pagination,
			);
		}
		echo $this->render(($view ? $view : $this->getClassName()), $vars);
	}

	/**
	 *  Alias fot index
	 *
	 * @since 1.0
	 */
	public function cancel($return_url = '')
	{
		if(!$return_url){
			$input = \JFactory::getApplication()->input;
			$return_url = 'index.php?option=' . $input->get('option') . '&controller=' . $input->get('controller',$this->getClassName()) . '&task=index';
		}
		\JFactory::getApplication()->redirect(\JRoute::_($return_url));
	}

	/**
	 * Method to support Joomla!Cms drag&drop ordering
	 *
	 * @param array $cid
	 * @param array $order
	 *
	 *
	 * @since 1.0
	 */
	public function saveOrderAjax(array $cid, array $order)
	{

		// Sanitize the input
		\JArrayHelper::toInteger($cid);
		\JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($cid, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		\JFactory::getApplication()->close();
	}

	protected function preRender($viewname, $layout, &$vars){
		if(class_exists(Helper::getClassParentNameSpacing($this).'\\Helper\\Sidebar')){
			call_user_func_array(array(Helper::getClassParentNameSpacing($this).'\\Helper\\Sidebar','setControllersEntries'),array($viewname,$layout));
		}
	}

	/**
	 * Rest responder | Don't blame me for this
	 *
	 * @param String  $data   data of response
	 * @param boolean $status is everything went good
	 *
	 * @since 1.0
	 */
	protected function respondJson($data, $status)
	{
		if (!$status)
		{
			echo "<pre>";
			print_r($data);
			echo "</pre>";
			header('HTTP/1.1 500 Internal Server Error');
			trigger_error("Issue in processing", E_USER_ERROR);
		}
		ob_start();
		$response       = new \stdClass();
		$response->data = $data;
		$log            = ob_get_contents();
		ob_end_clean();
		$response->log = $log;
		header('Content-Type: application/json');
		echo json_encode($response);
		exit();
	}


	protected function deletePrevPhotos($model, $data) {
		$key = $model->getKeyName();
		$model->load($data[$key]);
		foreach ($model as $cell) {
			if (is_array(json_decode($cell))) {
				foreach (json_decode($cell) as $file) {
					if (file_exists(JPATH_ROOT.$file)) {
						\JFile::delete(JPATH_ROOT.$file);
					}
				}
			}
		}
	}
}
