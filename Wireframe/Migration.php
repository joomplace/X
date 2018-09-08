<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 11.12.2017
 * Time: 13:45
 */

namespace Joomplace\X\Wireframe;


use Joomplace\X\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration extends Model
{
    protected $table = '#__x_migrations';

    protected $fillable = ['file'];

    public static function init()
    {
        $migrationModel = new self();
        if(!DB::schema()->hasTable($migrationModel->getTable())){
            DB::schema()->create($migrationModel->getTable(), function(Blueprint $table) {
                $table->increments('id');
                $table->string('file')->unique();
                $table->timestamps();
            });
        }
        return true;
    }
}