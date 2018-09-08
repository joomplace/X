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


use Joomplace\X\Model;

trait Dummy
{
    use Base;

    /**
     * @param string $action Action to check for
     * @param Model|null $context Item/context of the call
     * @param \Joomla\CMS\User\User|null $user User to check against
     * @return bool Is access allowed or not
     */
    public static function can($action, $context = null, \Joomla\CMS\User\User $user = null)
    {
        return true;
    }

    public function scopeAccessible($query)
    {
        return $query;
    }
}