<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;

class Loader{

    public static function loadByPsr4($class)
    {
        $files = self::extractExistingPaths($class);
        foreach ($files as $path){
            $return = include_once $path;
            if(class_exists($class, false)) {
                return (bool)$return;
            }
        }
        return false;
    }

    public static function findViewLayoutByNS($view, $layout, $ns){
        $app = \JFactory::getApplication();
        $template = $app->getTemplate();

        $paths = self::extractPaths($ns.'\\View\\'.$view,'/','view');
        foreach ($paths as $path){
            /*
             * TODO: add more extensions processing (php,html,twig)
             */
            $file = $path . DIRECTORY_SEPARATOR . $layout . '.php';
            if(file_exists($file)){
                return $file;
            }
        }
        return false;
    }

    public static function getPathByPsr4($class, $ext = 'php')
    {
        $files = self::extractExistingPaths($class, $ext);

        /*foreach ($files as $path){
            $classFilePath = $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classPathParts) . DIRECTORY_SEPARATOR . $classFile;
            $classFilePath = strtolower($classFilePath);

            if(file_exists($classFilePath) && !class_exists($class, false)){
                $return = include_once $classFilePath;
                return (bool) $return;
            }
        }*/

        return $files;
    }

    protected static function extractPaths($class, $ext = 'php', $override_logic = false)
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
        $classFile = $className . ($ext=='/'?'':('.' . $ext));
        // Applying logic of "vendor - extension type" on path
        $filePath = array();
        $classPathParts = array_filter(explode(DIRECTORY_SEPARATOR, $classPath));
        // If no $classPathParts then we have nothing to autoload by PSR-4
        // And everything that have no $classPathParts needs to register namespaces, also processed in PSR-0
        $return = array();
        $overridable_element = '';
        if ($classPathParts)
        {
            $vendor_check = ucfirst($classPathParts[0]);
            $type_check = ucfirst($classPathParts[1]);
            unset($classPathParts[0]);
            unset($classPathParts[1]);
            switch ($type_check){
                case 'Library':
                    $overridable_element = 'library';
                    $filePath[] = JPATH_LIBRARIES;
                    $filePath[] = JPATH_LIBRARIES . DIRECTORY_SEPARATOR . $vendor_check;
                    break;
                /*
                 * Component
                 */
                default:
                    $overridable_element = 'com_' . strtolower($type_check);
                    switch (ucfirst($classPathParts[2])){
                        case 'Admin':
                            $is_admin = true;
                            $filePath[] = JPATH_ADMINISTRATOR . '/components/' . 'com_' . strtolower($type_check);
                            break;
                        case 'Site':
                            $is_admin = false;
                            $filePath[] = JPATH_ROOT . '/components/' . 'com_' . strtolower($type_check);
                            break;
                    }
                    unset($classPathParts[2]);
            }

            $internal = implode(DIRECTORY_SEPARATOR, $classPathParts);
            $internal = $internal?(DIRECTORY_SEPARATOR . $internal):'';
            switch ($override_logic){
                case 'view':
                    /* getting specific app is pretty overcoding but let it be here, any way if we not in a zone - will return default zone template */
                    $app = \JFactory::getApplication((isset($is_admin) && $is_admin)?'administrator':'site');
                    $template = $app->getTemplate();
                    $root = ((isset($is_admin) && $is_admin)?JPATH_ADMINISTRATOR:JPATH_ROOT) . DIRECTORY_SEPARATOR;
                    $return[] = $root . 'templates' .  DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . 'html' . ($overridable_element?(DIRECTORY_SEPARATOR . $overridable_element):'') . DIRECTORY_SEPARATOR . $classFile;
                    break;
                default:
                    break;
            }
            foreach ($filePath as $path){
                $classFilePath = $path . $internal . DIRECTORY_SEPARATOR . $classFile;
                $classFilePath = strtolower($classFilePath);

                $return[] = $classFilePath;
            }
        }
        return $return;
    }

    protected static function extractExistingPaths($class, $ext = 'php'){
        $paths = self::extractPaths($class, $ext);
        foreach ($paths as $i => $path){
            if(!file_exists($path) && !($ext=='/' && is_dir($path))){
                unset($paths[$i]);
            }
        }
        return $paths;
    }
}

spl_autoload_register(array('Joomplace\\Library\\JooYii\\Loader', 'loadByPsr4'));