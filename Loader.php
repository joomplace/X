<?php
/**
 * Copyright (c) 2017. Alexandr Kosarev, @kosarev.by
 */

namespace Joomplace\X;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\AbstractPaginator;
use Joomla\CMS\Factory;

class Loader
{
    protected function __construct()
    {
        require_once __DIR__ . '/vendor/autoload.php';
        \JLoader::registerNamespace(__NAMESPACE__, __DIR__, false, false, 'psr4');
        self::registerPsr4Autoloader();
        if(version_compare(JVERSION,'4.0.0') < 0){
            class_alias(Legacy\Controller::class,'\\Joomplace\\X\\Controller');
            class_alias(Legacy\View::class,'\\Joomplace\\X\\View');
        }
        AbstractPaginator::currentPageResolver(function($var){
            return Factory::getApplication()->input->get($var);
        });
    }

    public static function boot()
    {
        static $instance = false;
        if(!$instance){
            $instance = new self();
        }
        return $instance;
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
        $capsule->setAsGlobal();
    }

    public static function registerPsr4Autoloader(){
        spl_autoload_register(array(self::class, 'loadByPsr4'));
    }

    protected static $vendors = array('Joomplace');
    /**
     * Allow to register 3rd party vendors
     *
     * @param string $vendor 3rd party vendor name
     *
     *
     * @since 1.0
     */
    public static function registerVendor($vendor)
    {
        if (!in_array($vendor, self::$vendors))
        {
            self::$vendors[] = $vendor;
        }
    }

    /**
     * Add compatibility for Joomla!CMS (3.x) routing system
     *
     * @param string $class Router class name
     *
     * @return bool
     *
     * @since 1.0
     */
//    public static function loadRouterForJoomla($class)
//    {
//        if (strpos($class, 'Router'))
//        {
//            $component = str_replace('Router', '', $class);
//            foreach (self::$vendors as $vendor)
//            {
//                $fqcn = $vendor . '\\' . $component . '\\Site\\Router';
//                if(class_exists($fqcn, false) || self::loadByPsr4($fqcn))
//                {
//                    class_alias($fqcn,$class);
//                    return true;
//                }
//            }
//        }
//        return false;
//    }
    /**
     * Class loading by PSR-4
     *
     * @param string $class Fully qualified class name
     *
     * @return bool Result
     *
     * @since 1.0
     */
    public static function loadByPsr4($class)
    {
        $files = self::extractExistingPaths($class);
        foreach ($files as $path)
        {
            $return = include_once $path;
            if (class_exists($class, false))
            {
                return (bool) $return;
            }
        }
        return false;
    }
    /**
     * Extract only existing paths by class name
     *
     * @param string $class          Fully qualified class name
     * @param string $ext            Files extension
     * @param bool   $override_logic Use override logic of ...
     *
     * @return array Array of absolute paths
     *
     * @since 1.0
     */
    protected static function extractExistingPaths($class)
    {
        $paths = self::extractPaths($class);
        foreach ($paths as $i => $path)
        {
            if (!file_exists($path))
            {
                unset($paths[$i]);
            }
        }
        return $paths;
    }

    /**
     * Extract paths by class name
     *
     * @param string $class          Fully qualified class name
     * @param string $ext            Files extension
     *
     * @return array Array of absolute paths
     *
     * @since 1.0
     */
    public static function extractPaths($class)
    {
        list($classFile, $filePath, $classPathParts, $return) = self::parseClass($class);
        if($filePath){
            $internal = implode(DIRECTORY_SEPARATOR, $classPathParts);
            $internal = $internal ? (DIRECTORY_SEPARATOR . $internal) : '';
            foreach ($filePath as $path)
            {
                $classFilePath = $path . $internal . DIRECTORY_SEPARATOR . $classFile;
                $return[] = $classFilePath;
            }
        }
        return $return;
    }

    /**
     * Proxy for extractExistingPaths
     *
     * @param string $class          Fully qualified class name
     * @param string $ext            Files extension
     *
     * @return array Array of absolute paths
     *
     * @since 1.0
     */
    public static function getPathByPsr4($class)
    {
        $paths = self::extractExistingPaths($class);
        return $paths;
    }

    /**
     * @param $class
     * @param $ext
     *
     * @return array
     *
     * @since 1.0
     */
    protected static function parseClass($class)
    {
        // Remove the root backslash if present.
        if ($class[0] == '\\')
        {
            $class = substr($class, 1);
        }
        // Find the location of the last NS separator.
        $pos = strrpos($class, '\\');
        // If one is found, we're dealing with a NS'd class.
        if ($pos !== false)
        {
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        }
        // If not, no need to parse path.
        else
        {
            $classPath = null;
            $className = $class;
        }
        $classFile = $className . '.php';
        // Applying logic of "vendor - extension type" on path
        $filePath       = array();
        $classPathParts = array_filter(explode(DIRECTORY_SEPARATOR, $classPath));
        // If no $classPathParts then we have nothing to autoload by PSR-4
        // And everything that have no $classPathParts needs to register namespaces, also processed in PSR-0
        $return              = array();

        if($classPathParts){
            $vendor = ucfirst(array_shift($classPathParts));
            $exType   = ucfirst(array_shift($classPathParts));

            // CLIENT . BASE_PATH - TYPE_FOLDER . (EX_NAME OR VENDOR + EX_NAME ) . ELSE
            $folds = array();
            switch ($exType)
            {
                case 'Plugin':
                    // plugins/type
                    $folds[] = JPATH_PLUGINS;
                    $folds[] = ucfirst(array_shift($classPathParts));
                    $ext = array_shift($classPathParts);
                    break;
                default:
                    $folds[] = JPATH_LIBRARIES;
                    $ext = array_shift($classPathParts);
                    break;
                case 'Module':
                case 'Component':
                    $ext = array_shift($classPathParts);
                    switch (array_shift($classPathParts)){
                        case 'Admin':
                            $folds[] = JPATH_ADMINISTRATOR;
                            break;
                        default:
                            $folds[] = JPATH_SITE;
                            break;
                    }
                    $folds[] =  lcfirst($exType).'s';
                    break;
            }
            $prfx = '';
            switch ($exType)
            {
                case 'Module':
                    $prfx = 'mod_';
                    break;
                case 'Component':
                    $prfx = 'com_';
                    break;
            }
            $basePath = implode(DIRECTORY_SEPARATOR,$folds);
            $filePath[] = $basePath . DIRECTORY_SEPARATOR . $prfx . lcfirst($ext);
            $filePath[] = $basePath . DIRECTORY_SEPARATOR . $prfx . lcfirst($vendor) . '_' . lcfirst($ext);
        }
        return array($classFile, $filePath, $classPathParts, $return);
    }
}