<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X\Renderer;

use Joomla\CMS\Factory;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Edge as EdgeEngine;
use Windwalker\Edge\Loader\EdgeFileLoader;

trait Edge
{
    function render($tpl = null)
    {
        $fileLoader = new EdgeFileLoader($this->_path['template']);
        static $cache = false;
        if($cache === false){
            if(Factory::getConfig()->get('caching',0)){
                if(Factory::getApplication()->isClient('administrator')){
                    $base_path = JPATH_ADMINISTRATOR;
                }else{
                    $base_path = JPATH_SITE;
                }
                $cache = new EdgeFileCache($base_path . '/cache/blade');
            }else{
                $cache = null;
            }
        }
        $renderer = new EdgeEngine($fileLoader,null, $cache);
        $compiler = $renderer->getCompiler();
        $compiler->directive('lang', function ($expression)
        {
            return "<?= \Joomla\CMS\Language\Text::sprintf$expression; ?>";
        });

        $compiler->directive('can', function ($expression)
        {
            $call = explode(',',trim($expression, '()'));
            $context = $call[1];
            return '<?php if('.$context.'::can('.implode(',', $call).')){ ?>';
        });
        $compiler->directive('endcan', function ($expression)
        {
            return "<?php } ?>";
        });

        $compiler->directive('cannot', function ($expression)
        {
            $call = explode(',',trim($expression, '()'));
            $context = $call[1];
            return '<?php if(!'.$context.'::can('.implode(',', $call).')){ ?>';
        });
        $compiler->directive('endcannot', function ($expression)
        {
            return "<?php } ?>";
        });

        $compiler->directive('jtoolbar', function ($expression)
        {
            $call = explode(',',trim($expression, '()'));
            $call[0] = trim($call[0],' \'');
            if(!isset($call[1])){
                $call[1] = [];
            }
            return "<?php call_user_func_array([\JToolbarHelper::class,'$call[0]'],$call[1]); ?>";
        });
        return $renderer->render($tpl ? ($this->getLayout() . '_' . $tpl) : $this->getLayout(), $this->getProperties());
    }

}