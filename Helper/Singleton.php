<?php
/**
 * Copyright (c) 2019. Alexandr Kosarev, @kosarev.by
 */

/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 24.03.2019
 * Time: 0:15
 */

namespace Joomplace\X\Helper;


trait Singleton
{
    private static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
        parent::__clone();
    }

    private function __construct() {
        parent::__construct();
    }
}
