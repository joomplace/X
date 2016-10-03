<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomplace\Library\JooYii;


class Form extends \JForm
{
    public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
    {
        // Reference to array with form instances
        $forms = &self::$forms;

        // Only instantiate the form if it does not already exist.
        if (!isset($forms[$name]))
        {
            $data = trim($data);

            if (empty($data))
            {
                throw new InvalidArgumentException(sprintf('JForm::getInstance(name, *%s*)', gettype($data)));
            }

            // Instantiate the form.
            $forms[$name] = new Form($name, $options);

            // Load the data.
            if (substr($data, 0, 1) == '<')
            {
                if ($forms[$name]->load($data, $replace, $xpath) == false)
                {
                    throw new RuntimeException('JForm::getInstance could not load form');
                }
            }
            else
            {
                if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
                {
                    throw new RuntimeException('JForm::getInstance could not load file');
                }
            }
        }

        return $forms[$name];
    }

    protected function loadFieldType($type, $new = true)
    {
        $return = parent::loadFieldType($type, $new);
        if(!$return){
            if(class_exists($type)){
                return new $type();
            }
            return false;
        }
        return $return;
    }


}