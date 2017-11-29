<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;

use Illuminate\Database\Capsule\Manager as Capsule;

class Loader
{
    public function __construct()
    {
        require_once __DIR__ . '/vendor/autoload.php';
        \JLoader::registerNamespace(__NAMESPACE__, __DIR__, false, false, 'psr4');
    }

    public static function boot()
    {
        return (new self()) ? true : false;
    }

    public static function bootDatabase($config = null)
    {
        if (!$config) {
            $config = \Joomla\CMS\Factory::getConfig();
        }

        $capsule = new Capsule;

        $dbtype = null;
        switch ($config->get('dbtype')) {
            default:
                $dbtype = $config->get('dbtype');
            case 'mysqli':
                $dbtype = 'mysql';
                break;
        }

        $capsule->addConnection(array(
            'driver' => $dbtype,
            'host' => $config->get('host'),
            'database' => $config->get('db'),
            'username' => $config->get('user'),
            'password' => $config->get('password'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $config->get('dbprefix')
        ));

        $capsule->bootEloquent();
    }
}