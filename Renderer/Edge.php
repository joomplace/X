<?php
/**
 * Copyright (c) 2018. JoomPlace, all rights reserved
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
        $renderer->setGlobals(['view' => $this->getName()]);
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
            if(!isset($call[1])){
                $call[1] = [];
            }
            return "<?php call_user_func_array([\JToolbarHelper::class,$call[0]],$call[1]); ?>";
        });
        return $renderer->render($tpl ? ($this->getLayout() . '_' . $tpl) : $this->getLayout(), $this->getProperties());
    }

    public function stored($item){
        $this->redirectToList();
    }

    public function updated($item)
    {
        $this->redirectToList();
    }

    public function destroyed($item){
        $this->redirectToList();
    }

    protected function redirectToList()
    {
        $query = ['option' => Factory::getApplication()->input->get('option'), 'view' => $this->getName()];
        foreach ($query as $k => $v) {
            $query[$k] = $k . '=' . $v;
        }
        Factory::getApplication()->redirect(trim(implode('?',array_filter([
            \Joomla\CMS\Uri\Uri::getInstance()->getPath(),
            implode('&', array_values($query))
        ])),'/'));
    }
}