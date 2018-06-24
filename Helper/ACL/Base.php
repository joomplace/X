<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 26.12.2017
 * Time: 16:06
 */

namespace Joomplace\X\Helper\ACL;


trait Base
{
    protected $context = null;

    public function getParentAssetName(){
        if(!$this->context){
            list($vendor, $ext, $name) = explode('\\',self::class);
            $this->context = implode('.',[strtolower(substr($ext,0,3).'_'.$name),$this->getTable()]);
        }
        return $this->context;
    }

    public static function getAssetName($context = null){
        if(!is_subclass_of($context, Model::class,false)){
            $context = new static();
            return $context->getParentAssetName();
        }
        return implode('.',[$context->getParentAssetName(),$context->getKey()]);
    }

    public abstract static function can($action, $context = null, \Joomla\CMS\User\User $user = null);

    public abstract function scopeAccessible($query);
}