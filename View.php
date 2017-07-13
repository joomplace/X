<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 22.06.2017
 * Time: 15:34
 */

namespace JoomPlaceX;


class View extends \JViewLegacy
{
    public function render($tpl = null){
        \jimport('joomla.filesystem.path');

        $layout = $this->getLayout()?$this->getLayout():'default';
        $filetofind = $this->_createFileName('template', array('name' => $layout));
        $file = \JPath::find($this->_path['template'], $filetofind);
        if($file){
            $output = parent::loadTemplate($tpl);
        }else{
            $output = \JLayoutHelper::render($layout?$layout:'default',$this,dirname(__FILE__).DIRECTORY_SEPARATOR.'views');
        }
        return $output;
    }
}