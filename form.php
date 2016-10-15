<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

/**
 * Class for using instead of \JForm class
 * as \JForm doesn't suite some needed behavior
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
class Form extends \JForm
{
	/**
	 * Override to return instances of Form instead of /JForm
	 *
	 * @param   string         $name      The name of the form.
	 * @param   string         $data      The name of an XML file or string to load as the form definition.
	 * @param   array          $options   An array of form options.
	 * @param   boolean        $replace   Flag to toggle whether form fields should be replaced if a field
	 *                                    already exists with the same group/name.
	 * @param   string|boolean $xpath     An optional xpath to search for the fields.
	 *
	 * @return Form
	 *
	 * @since version
	 */
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

	/**
	 * Override to add ability to load FieldType by fully qualified class name
	 *
	 * @param string $type Joomla registered types or fully qualified class name
	 * @param bool   $new  force new Instance
	 *
	 * @return bool|\JFormField
	 *
	 * @since 1.0
	 */
	protected function loadFieldType($type, $new = true)
	{
		$return = parent::loadFieldType($type, $new);
		if (!$return)
		{
			if (class_exists($type))
			{
				return new $type();
			}

			return false;
		}

		return $return;
	}


}