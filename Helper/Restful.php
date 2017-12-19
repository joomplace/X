<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 13.12.2017
 * Time: 15:30
 */

namespace Joomplace\X\Helper;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomplace\X\Model;
use Joomplace\X\View;

trait Restful
{
    use Injector;

    public function execute($task){
        $method = strtoupper($this->injectArg('httpxmethod',$_SERVER['REQUEST_METHOD']));
        $methodMap = [
            'store'=>'POST',
            'update'=>'PUT',
            'destroy'=>'DELETE',
            'show' => 'GET',
            'index' => 'GET',
        ];
        if(!$task){
            switch ($method){
                case 'GET':
                    if($this->injectArg('id')){
                        $task = 'show';
                    }else{
                        $task = 'index';
                    }
                    break;
                default:
                    $task = array_search($method,$methodMap);
            }
        }else{
            if(array_key_exists($task,$methodMap) && $methodMap[$task]!=$method){
                throw new \Exception("Incorrect method. Expecting '$methodMap[$task]', and got '$method'.", 500);
            }
        }

        if(!$this->methodDefined($task)){
            if(array_key_exists($task,$methodMap) || in_array($task,array('create','edit'))){
                $this->taskMap[$task] = 'trait'.ucfirst($task);
            }
        }

        parent::execute($task);
    }

    public function traitIndex(){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        $view->items = $modelClass::query()->paginate($this->input->get('limit',Factory::getConfig()->get('list_limit',20)));
        return $view->render();
    }

    public function traitShow($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        $view->item = $modelClass::findOrFail($id);
        return $view->render();
    }

    public function traitCreate(){
        /** @var View $view */
        $view = $this->getView();
        $view->setLayout('create');
        return $view->render();
    }

    public function traitEdit($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        $view->setLayout('edit');
        $view->item = $modelClass::findOrFail($id);
        return $view->render();
    }

    public function traitStore(){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::create($this->getInput()->getArray());
        $option = $this->injectArg('option');
        $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_CREATED',$item->id));
        return $this->setRedirect('index.php?option='.$option);
    }

    public function traitUpdate($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        $item->fill($this->getInput()->getArray());
        $item->saveOrFail();
        $item = $modelClass::findOrFail($id);

        $option = $this->injectArg('option');
        $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_UPDATED',$item->id));
        return $this->setRedirect('index.php?option='.$option);
    }

    public function traitDestroy($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        $item->delete();

        $option = $this->injectArg('option');
        $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_DELETED',$item->id));
        return $this->setRedirect('index.php?option='.$option);
    }
}