<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 23.06.2017
 * Time: 14:22
 */

namespace JoomPlaceX;


class Sidebar extends \JHtmlSidebar
{
    public static function getEntries()
    {
        $entries = parent::getEntries();
        $entries = static::getCascadeEntries($entries);
        return $entries;
    }

    protected static function getCascadeEntries($entries, $name_prefix = '')
    {
        $result = array();
        foreach ($entries as $entry){
            $entry[0] = $name_prefix.$entry[0];
            $result[] = $entry;
            if(isset($entry[3]) && $entry[3]){
                $result = array_merge($result, static::getCascadeEntries($entry[3], $name_prefix.' - '));
            }
        }
        return $result;
    }

    protected static function addEntryCascade(&$parentNode, $name, $link = '', $active = false)
    {
        $add = true;
        $parent = '';
        if(strpos($name,' | ')){
            $complex = explode(' | ',$name);
            $parent = array_shift($complex);
            $name = implode(' | ',$complex);
        }
        foreach ($parentNode[3] as &$entry){
            if($parent && $entry[0]==$parent){
                if(!isset($entry[3])){
                    $entry[3] = array();
                }
                static::addEntryCascade($entry, $name, $link, $active);
                $add = false;
                break;
            }
        }
        if($add){
            foreach ($parentNode[3] as &$entry){
                if($entry[0]==$name){
                    $entry = array($name, $link, $active);
                    $add = false;
                    break;
                }
            }
        }
        if($add){
            $parentNode[3][] = array($name, $link, $active);
        }
    }

    public static function addEntry($name, $link = '', $active = false)
    {
        $parentNode = array();
        $parentNode[3] = &static::$entries;
        static::addEntryCascade($parentNode, $name, $link, $active);
    }


}