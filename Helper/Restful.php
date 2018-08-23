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

        return parent::execute($task);
    }

    public function traitIndex(){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        $view->items = $modelClass::accessible()->paginate($this->input->get('limit',Factory::getConfig()->get('list_limit',
            20)));
        return $view->render();
    }

    public function traitShow($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        if($modelClass::can('view',$item)){
            $view->item = $item;
            return $view->render();
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }

    public function traitCreate(){
        /** @var View $view */
        $view = $this->getView();
        $view->setLayout('create');
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        if($modelClass::can('create')){
            return $view->render();
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }

    public function traitEdit($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        $view = $this->getView();
        $view->setLayout('edit');
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        if($modelClass::can('edit',$item)){
            $view->item = $item;
            return $view->render();
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }

    public function traitStore(){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        if($modelClass::can('create',new $modelClass)){
            /** @var Model $item */
            $item = $modelClass::create($modelClass::getFillFromInput($this->getInput()));
            $option = $this->injectArg('option');
            $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_CREATED',$item->id));
            $view = $this->getView();
            return $view->stored($item);
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }

    public function traitUpdate($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        if($modelClass::can('update',$item)){
            $item->fill($modelClass::getFillFromInput($this->getInput()));
            $item->saveOrFail();

            $option = $this->injectArg('option');
            $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_UPDATED',$item->id));
            $view = $this->getView();
            return $view->updated($item);
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }

    public function traitDestroy($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        if($modelClass::can('delete',$item)){
            $item->delete();

            $option = $this->injectArg('option');
            $this->app->enqueueMessage(Text::sprintf(strtoupper($option).'_DELETED',$item->id));
            $view = $this->getView();
            return $view->destroyed($item);
        }else{
            throw new \Exception(Text::_('JOOMPLACE_X_NOT_ALLOWED'), 403);
        }
    }
}