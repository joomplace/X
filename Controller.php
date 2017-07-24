<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 22.06.2017
 * Time: 15:26
 */

namespace JoomPlaceX;


use Joomla\Registry\Registry;

class Controller extends \JControllerBase
{
    protected $_default_action = 'display';

    protected $_actions_map
        = array(
            'save'      => 'apply',
            'save2copy' => 'apply',
            'save2new'  => 'apply',
        );

    public function execute($action = null)
    {
        if (!$action) {
            $action = $this->_default_action;
        }

        if (!$this->methodExists($action) && isset($this->_actions_map[$action])) {
            $action = $this->_actions_map[$action];
        }

        $arguments = array();
        foreach ($this->getArgs($action) as $param) {
            /** @var \ReflectionParameter $param */
            /*
             * TODO: set initial filter from config
             */
            $filter = 'RAW';
            if ($param->isArray()) {
                $filter = 'array';
            }

            $value = $this->input->get($param->name, null, $filter);

            if ($value === null) {
                if (!$param->isDefaultValueAvailable()) {
                    // TODO: improve
                    throw new \Exception("Need to define $param->name", 500);
                } else {
                    $value = $param->getDefaultValue();
                }
            }

            $arguments[] = $value;
        }

        if ($this->methodExists($action)) {
            return call_user_func_array(array($this, $action), $arguments);
        } else {
            // TODO: improve
            throw new \Exception("Method `" . $this->getClassName() . '::'
                . $action . "` doesn't exist");
        }
    }

    protected function getArgs($method)
    {
        if ($this->methodExists($method)) {
            $ref    = new \ReflectionMethod($this->getClassName(), $method);
            $result = $ref->getParameters();
        } else {
            $result = array();
        }

        return $result;
    }

    protected function methodExists($method, $in_target = false)
    {
        if (method_exists($this->getClassName(), $method)) {
            if ($in_target) {
                $ref = new \ReflectionMethod($this->getClassName(), $method);
                if (strtolower($ref->getDeclaringClass()->name)
                    == strtolower($this->getClassName())
                ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function getClassName()
    {
        return get_called_class();
    }

    public function index(array $filter = array())
    {
        $filter = array_filter($filter, function($v){
            return $v?true:($v===''?false:true);
        });
        /** @var \JoomplaceX\Model $model */
        $model = $this->getModel();
        $vars  = array(
            'columns'    => $model->getColumns('list'),
            'state'      => $model->getState(),
            'filter'     => $filter,
            'items'      => $model->getList(false, false, $filter),
            'pagination' => $model->getPagination(),
        );
        $this->display(lcfirst($this->getClassShortName()), $vars);
    }

    /**
     * @param null $class  Model Class name
     * @param null $config Config to pass to constructor of model
     *
     * @return Model
     *
     * @since 2.0
     */
    protected function getModel($class = null, $config = null)
    {
        if (strpos($class, '\\') === false) {
            $ns                 = explode('\\', $this->getClassName());
            $ns[count($ns) - 2] = 'Model';
            if ($class) {
                $ns[count($ns) - 1] = $class;
            }
            $class = implode('\\', $ns);
        }

        return $model = new $class($config);
    }

    public function display($view, $vars = array(), $layout = 'default')
    {
        $type   = \JFactory::getDocument()->getType();
        $output = $this->render($view, $type, $vars, $layout);
        switch ($type) {
            case 'json':
                $output = json_encode($output);
                echo $output;
                break;
            case 'pdf':
                // TODO: set content type and modify document or even die
            default:
                echo $output;
        }
    }

    public function render($view, $type, $vars = array(), $layout = 'default')
    {
        if(strpos($view,'.')){
            list($view, $layout) = explode('.', $view);
        }
        $viewConfig = array(
            'name'   => $view,
            'layout' => $layout
        );
        $ns         = explode('\\', $this->getClassName());

        array_pop($ns);
        array_pop($ns);

        $viewClass = implode('\\', $ns) . '\views\\' . $view . '\\'
            . ucfirst($type);
        if (class_exists($viewClass)) {
            /** @var \JoomplaceX\View $view */
            $view = new $viewClass($viewConfig);
            foreach ($vars as $k => $v) {
                $view->set($k, $v);
            }
            $output = $view->render(null);
        } else {
            /** @var \JViewLegacy $view */
            $view = new \JViewLegacy($viewConfig);
            foreach ($vars as $k => $v) {
                $view->set($k, $v);
            }
            $output = $view->loadTemplate(null);
        }

        return $output;
    }

    public function getClassShortName($class = null)
    {
        if ($class == null) {
            $class = $this->getClassName();
        }
        $path = explode('\\', $class);

        return array_pop($path);
    }

    public function add()
    {
        $this->edit(array(0));
    }

    public function edit(array $cid)
    {
        $id = array_shift($cid);
        $model = $this->getModel(null, $id);
        $vars  = array(
            'item' => $model,
        );
        $this->display(lcfirst($this->getClassShortName()) . '.edit', $vars);
    }

    public function apply(array $jform, $task)
    {

        /** @var Model $model */
        $model = $this->getModel();
        $model->load($jform[$model->getKeyName()]);
        if ($task == 'save2copy') {
            $jform[$model->getKeyName()] = '';
        }
        if ($id = $this->storeRecord($jform)) {
            $jform[$model->getKeyName()] = $id;
        }
        switch ($task) {
            case 'save':
                $this->cancel();
                break;
            case 'save2new':
                $id = 0;
            case 'save2copy':
            default:
                if ($id) {
                    $this->app->redirect(\JRoute::_('index.php?option='
                        . $this->input->get('option') . '&view='
                        . $this->input->get('view') . '&task=edit&cid[]=' . $id,
                        false));
                } else {
                    $this->app->redirect(\JRoute::_('index.php?option='
                        . $this->input->get('option') . '&view='
                        . $this->input->get('view') . '&task=add', false));
                }
        }
    }

    protected function storeRecord(array $jform)
    {
        /** @var Model $model */
        $model = $this->getModel();
        $form  = $model->getForm();
        // TODO: filter but workaround issue with form filtering out fielded data
//        $jform = $form->filter($jform);
        /** @var \Joomla\Registry\Registry $data */
        $form->bind($jform);
        /* recursive jform */
        $rjform = $form->getData()->toArray();
        $return = $form->validate($rjform);
        if (!$return) {
            \JFactory::getApplication()
                ->setUserState($model->getContext(), $rjform);
            array_map(function ($e) {
                \JFactory::getApplication()
                    ->enqueueMessage($e->getMessage(), 'error');
            }, $form->getErrors());

            return false;
        } else {
            if (!$model->save($jform)) {
                return false;
            } else {
                \JFactory::getApplication()
                    ->setUserState($model->getContext(), null);
            }
        }
        $key = $model->getKeyName();

        return $model->$key;
    }

    public function cancel()
    {
        $this->app->redirect(\JRoute::_('index.php?option='
            . $this->input->get('option') . '&view='
            . $this->input->get('view'), false));
    }

    public function delete(array $cid)
    {
        $model         = $this->getModel();
        $unprocessed   = array_filter(array_map(function ($id) use ($model) {
            if ($model->delete($id)) {
                return false;
            } else {
                return $id;
            }
        }, $cid));
        $count_deleted = count($cid) - count($unprocessed);
        if ($count_deleted) {
            \JFactory::getApplication()
                ->enqueueMessage(\JText::plural(strtoupper($this->input->get('option')
                    . '_' . $this->getClassShortName() . '_N_DELETED'),
                    $count_deleted));
        }
        if (count($unprocessed)) {
            \JFactory::getApplication()
                ->enqueueMessage(\JText::plural(strtoupper($this->input->get('option')
                    . '_' . $this->getClassShortName() . '_N_NOT_DELETED'),
                    count($unprocessed)), 'error');
            // TODO: Process undeleted checkboxes checkboxes
        }
        $this->cancel();
    }

    public function unpublish(array $cid)
    {
        $this->publish($cid, 0);
    }

    public function publish(array $cid, $state = 1)
    {
        $model         = $this->getModel();
        $unprocessed   = array_filter(array_map(function ($id) use (
            $model,
            $state
        ) {
            if ($model->publish($id, $state)) {
                return false;
            } else {
                return $id;
            }
        }, $cid));
        $count_deleted = count($cid) - count($unprocessed);
        if ($count_deleted) {
            \JFactory::getApplication()
                ->enqueueMessage(\JText::plural(strtoupper($this->input->get('option')
                    . '_' . $this->getClassShortName() . '_N_' . ($state ? ''
                        : 'UN') . 'PUBLISHED'), $count_deleted));
        }
        if (count($unprocessed)) {
            \JFactory::getApplication()
                ->enqueueMessage(\JText::plural(strtoupper($this->input->get('option')
                    . '_' . $this->getClassShortName() . '_N_NOT_' . ($state
                        ? '' : 'UN') . 'PUBLISHED'), count($unprocessed)),
                    'error');
            // TODO: Process undeleted checkboxes checkboxes
        }
        $this->cancel();
    }

    protected function methodDefined($method)
    {
        return $this->methodExists($method, true);
    }
}