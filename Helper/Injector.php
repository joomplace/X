<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 13.12.2017
 * Time: 15:44
 */

namespace Joomplace\X\Helper;


use Joomla\CMS\Factory;
use Joomla\Input\Input;

trait Injector
{
    protected function injectRequiredArg($arg, $filter = 'RAW', $input = null)
    {
        $input = $this->getInput($input);

        if ($input->get($arg) == null) {
            // TODO: improve
            throw new \Exception("Injecting undefined $arg", 500);
        } else {
            return $this->injectArg($arg, null, $filter, $input);
        }
    }

    protected function getInput($input = null){
        if($input === null){
            if(isset($this->input)){
                $input = $this->input;
            }else{
                $input = Factory::getApplication()->input;
            }
            $jsonInput = json_decode(file_get_contents('php://input'));
            foreach ($jsonInput as $k => $v){
                $input->def($k,$v);
            }
        }
        return $input;
    }

    protected function injectArg($arg, $default = null, $filter = 'RAW', $input = null)
    {
        $input = $this->getInput($input);

        if ($input instanceof Input) {
            $value = $input->get($arg, $default, $filter);
        } else {
            $value = (new \Joomla\Filter\InputFilter)->clean($input->get($arg, $default), $filter);
        }

        return $value;
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

    protected function methodDefined($method)
    {
        return $this->methodExists($method, true);
    }

}