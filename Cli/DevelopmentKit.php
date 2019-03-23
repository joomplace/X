<?php
/**
 * Copyright (c) 2019. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 25.01.2019
 * Time: 16:12
 */

namespace Joomplace\X\Cli;


use Joomla\Filesystem\File;

class DevelopmentKit
{
    public static function help($command = null){
        echo "Usage:\n php DevekiomentKit.php \e[0;32;48mcomponent command \e[0;31;48margument ... argument\e[0m\n";
        $commands = [];
        $commands['help'] = " - Print out this help.";
        $commands['generate'] = " - Use to send different generate commands.";
        echo "This is the command list:\n";
        foreach ($commands as $cmd => $desc){
            echo "\e[0;32;48m$cmd\e[0m".$desc."\n";
        }
    }

    public static function process($component, $args){
        if($args[0]=='generate'){
            $func = $args[0].strtoupper($args[1]);
            self::$func($component, $args[2]);
        }
    }

    protected static function generateMigrations($component,$name){
        $template = file_get_contents(__DIR__.'/templates/migration.php.tmpl');
        $template = str_replace('{{name}}',$name,$template);
        $filename = date('Y_m_d_His').strtolower(preg_replace('/([A-Z])/','_$1',$name)).'.php';
        file_put_contents(JPATH_SITE.'/administrator/components/com_'.$component.'/migrations/'.$filename,$template);
    }
}