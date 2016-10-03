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

	public function __construct()
	{
        if(!class_exists('JToolbarHelper')){
            jimport('includes.toolbar',JPATH_ADMINISTRATOR);
        }
		$this->setModel($this->getClassName());
	}

	protected function getClassName(){
		return Helper::getClassName($this);
	}

    protected function getClassParentNameSpacing(){
        return Helper::getClassParentNameSpacing($this);
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
        $model = $this->getModel();
        $vars = array();
        if($model){
            $state = $model->getState();
            if($limit !== false){
                $items = $model->getList($limitstart,$limit);
            }else{
                $items = $model->getList();
            }
            $pagination = $model->getPagination();
            $vars = array(
                'state' => $state,
                'items' => $items,
                'pagination' => $pagination,
            );
        }
        echo $this->render($this->getClassName(),$vars);
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

    public function add(){
        $this->edit();
    }

    public function edit(array $cid = array()){
        $model = $this->getModel();
        if($cid){
            $model->load($cid[0]);
            \JToolbarHelper::title(\JText::_(strtoupper($this->getClassName()).'_EDIT_TITLE'), 'pencil');
        }else{
            \JToolbarHelper::title(\JText::_(strtoupper($this->getClassName()).'_NEW_TITLE'), 'pencil-2');
        }
        $vars = array(
            'item' => $model,
        );

        $key = $model->getKeyName();
        \JToolbarHelper::apply('apply');
        \JToolbarHelper::save('save');
        if($model->$key){
            \JToolbarHelper::save2copy('save2copy');
        }
        \JToolbarHelper::save2new('save2new');
        \JToolbarHelper::cancel('cancel');

        echo $this->render('form',$vars);
    }

    public function apply(array $jform, $tonew = false){
        $model = $this->getModel();
        $model->save($jform);
        if(!$tonew){
            $key = $model->getKeyName();
            $this->edit(array($model->$key));
        }else{
            $this->edit();
        }
    }

    public function saveandnew(array $jform){
        $this->apply($jform, true);
    }

    public function save2copy(array $jform){
        $jform['id'] = '';
        $this->apply($jform);
    }

    public function save(array $jform){
        $model = $this->getModel();
        $model->save($jform);
        $this->index();
    }

    public function cancel(){
        $this->index();
    }

    public function saveOrderAjax(array $cid, array $order){

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

    /**
     * Rest responder | Don't blame me for this
     *
     * @param String    $data     data of response
     * @param boolean   $status   is everything went good
     *
     * @since version
     */
    protected function respondJson($data, $status){
        if(!$status){
            trigger_error("Issue in processing", E_USER_ERROR);
        }
        ob_start();
        $response = new \stdClass();
        $response->data = $data;
        $log = ob_get_contents();
        ob_end_clean();
        $response->log = $log;
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

}