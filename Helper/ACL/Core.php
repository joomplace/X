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


use Joomla\CMS\Factory;
use Joomplace\X\Model;

trait Core
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
        if(!$user){
            $user = Factory::getUser();
        }
        if($action){
            return $user->authorise('core.'.$action, static::getAssetName($context));
        }
        return false;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessible($query)
    {
//        $assetParent = static::getAssetName(null);
//        $currentTale = $this->getTable();
//        $db = $query->getConnection();
//        $query->leftJoin('assets', function($join) use ($assetParent, $currentTale, $db) {
//            $join->on('assets.name', '=', $db->raw('CONCAT("'.$assetParent.'.'.'",'.Factory::getConfig()->get('dbprefix').$currentTale.'.id)'));
//        });
//        $user = Factory::getUser();
//        $query->where(function($q)use($user){
//            /** @var \Illuminate\Database\Eloquent\Builder $q */
//            foreach ($user->getAuthorisedGroups() as $group){
//                $q->orWhere('assets.rules','LIKE','"'.$group.'":1');
//            }
//        });
//        $parentLevelAccess = static::can('view');
//        $query->orWhere(function($q)use($db,$parentLevelAccess){
//            /** @var \Illuminate\Database\Eloquent\Builder $q */
//            $q->whereNull('assets.id')->where($db->raw($parentLevelAccess),1);
//        });

        return $query;
    }
}