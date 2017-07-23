<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 23.06.2017
 * Time: 14:22
 */

namespace JoomPlaceX;


class Helper
{
    public static function renderLayout($layoutId, $data = array(), $path = null, $component, $nestingLevel = 1, $method = 'render'){
        $layout = new \JLayoutFile($layoutId);
        $layout->setComponent($component);
        $iPs = $layout->getIncludePaths();
        $iPs[] = $path;
        $layout->setIncludePaths($iPs);
        $html = $layout->$method($data);
        if(!$html && --$nestingLevel){
            $layoutId = explode('.',$layoutId);
            array_pop($layoutId);
            $layoutId = implode('.',$layoutId);
            $html = self::renderLayout($layoutId,$data,$path,$component,$nestingLevel,$method);
        }
        return $html;
    }
}