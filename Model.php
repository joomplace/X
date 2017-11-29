<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        Loader::bootDatabase();
        parent::__construct($attributes);
    }

    public function getTable()
    {
        if (isset($this->table)) {
            return str_replace('#__', '', $this->table);
        } else {
            return parent::getTable();
        }
    }

}