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
use Joomplace\X\Model;

trait Restful
{
    use Injector;

    public function execute($task){
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
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
            if($methodMap[$task]!=$method){
                throw new \Exception("Incorrect method. Expecting '$methodMap[$task]', and got '$method'.", 500);
            }
        }

        if(!$this->methodDefined($task) && array_key_exists($task,$methodMap)){
            $this->taskMap[$task] = 'trait'.ucfirst($task);
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

    public function traitStore($id = null){
        return 'That\'s TODO, id:'.$id;
    }

    public function traitUpdate($id){
        /** @var Model $modelClass */
        $modelClass = $this->getModel();
        /** @var Model $item */
        $item = $modelClass::findOrFail($id);
        $item->fill($this->getInput()->getArray());
        $item->saveOrFail();
        $item = $modelClass::findOrFail($id);
        $view = $this->getView();
        $view->item = $item;
        return $view->render();
    }

    public function traitDestroy($id){
        return 'That\'s TODO, id:'.$id;
    }
}