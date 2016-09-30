<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;

use \ReflectionMethod;

class Helper
{
    protected static $_classreflections = array();

    public static function getClassName($obj){
        $class = get_class($obj);
        if(!isset(self::$_classreflections[$class])){
            self::saveClassReflection($obj);
        }
        return self::$_classreflections[$class]->getShortName();
    }

    protected static function saveClassReflection($obj){
        $class = get_class($obj);
        self::$_classreflections[$class] = new \ReflectionClass($obj);
    }

    public static function getClassParentNameSpacing($obj){
        $class = get_class($obj);
        if(!isset(self::$_classreflections[$class])){
            self::saveClassReflection($obj);
        }
        return substr(self::$_classreflections[$class]->getNamespaceName(), 0, strrpos(self::$_classreflections[$class]->getNamespaceName(), '\\'));
    }

    public static function getClassNameSpace($obj){
        $class = get_class($obj);
        if(!isset(self::$_classreflections[$class])){
            self::saveClassReflection($obj);
        }
        return self::$_classreflections[$class]->getNamespaceName();
    }

    public static function trimText($text, $length = 35){
        if (strlen($text) > $length){
            $array = explode("|||", wordwrap($text, $length, "|||"));
            return array_shift($array) . "...";
        }else{
            return $text;
        }
    }

    public static function callBindedFunction($class, $method, $inputs = array()){

        if(!$inputs){
            $inputs = array(
                \JFactory::getApplication()->input,
            );
        }

        $arguments = array();
        $ref = new ReflectionMethod($class, $method);
        foreach( $ref->getParameters() as $param) {
            /** @var \ReflectionParameter $param */
            /*
             * TODO: set initial filter from config
             */
            $filter = 'RAW';
            if($param->isArray()){
                $filter = 'array';
            }
            foreach ($inputs as $input){
                if (!($input instanceof \Joomla\Registry\Registry || $input instanceof \JInput)) {
                    throw new \InvalidArgumentException('Input must be instanceof JRegestry');
                    return false;
                }
                $value = $input->get($param->name,null, $filter);
                if($value !== null){
                    break;
                }
            }
            if($value===null){
                if(!$param->isDefaultValueAvailable()){
                    trigger_error("Need to define $param->name", E_USER_ERROR);
                }else{
                    $value = $param->getDefaultValue();
                }
            }
            $arguments[] = $value;
        }
        return call_user_func_array(array($class, $method), $arguments);
    }
}