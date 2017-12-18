<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 11.12.2017
 * Time: 12:22
 */

namespace Joomplace\X\Helper;


use Illuminate\Database\Migrations\Migration;

trait Migrations
{
    public static function migrate($path) {
        \Joomplace\X\Wireframe\Migration::init();
        $files = array_map(function($file){
            return str_replace(JPATH_ROOT.DIRECTORY_SEPARATOR,'',$file);
        },glob($path.'/*.php'));
        \Joomplace\X\Wireframe\Migration::query()->whereIn('file',$files)->get(['file'])->map(function($migration) use
        (&$files){
            unset($files[array_search($migration->file,$files)]);
        });
        foreach ($files as $file) {
            require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.$file);
            $class = basename($file, '.php');
            $class = preg_replace_callback('/^(?:\d{4}_\d{2}_\d{2}_\d{6}_)?(.*?)$/',function(array $matches){
                return implode('',array_map(function($a){
                    return ucfirst($a);
                },explode('_',$matches[1])));
            },$class);
            /** @var Migration $migration */
            $migration = new $class;
            $migration->up();
            \Joomplace\X\Wireframe\Migration::create(['file'=>$file]);
        }
    }
}