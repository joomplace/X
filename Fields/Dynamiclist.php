<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Fields
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii\Fields;

jimport('joomla.form.helper');
\JFormHelper::loadFieldClass('list');
/**
 * Dynamic list field type
 *
 * @package     Joomplace\Library\JooYii\Fields
 *
 * @since       1.0
 */
class DynamicList extends \JFormFieldList
{
	/**
	 * Fully qualified class name
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $type = '\\Joomplace\\Library\\JooYii\\Fields\\DynamicList';
	/** @var array $_options Options store */
	protected $_options = array();

	/**
	 * Method to add option to list start
	 *
	 * @param        $key
	 * @param        $value
	 * @param bool   $selected
	 * @param bool   $disabled
	 * @param string $class
	 *
	 *
	 * @since 1.0
	 */
	public function prependOption($key, $value, $selected = false, $disabled = false, $class = '')
	{
		array_unshift($this->_options, array('value' => $key, 'text' => $value, 'selected' => $selected, 'checked' => $selected, 'disabled' => $disabled, 'class' => $class));
	}

	/**
	 * Method to get list options
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getOptions()
	{
		$fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
		$options   = array();

		foreach ($this->_options as $option)
		{
			// Filter requirements
			if (isset($option['requires']))
			{
				if ($requires = explode(',', (string) $option['requires']))
				{
					// Requires multilanguage
					if (in_array('multilanguage', $requires) && !\JLanguageMultilang::isEnabled())
					{
						continue;
					}

					// Requires associations
					if (in_array('associations', $requires) && !\JLanguageAssociations::isEnabled())
					{
						continue;
					}
				}
			}

			$value    = (string) $option['value'];
			$text     = (string) $option['text'];
			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
			$disabled = $disabled || ($this->readonly && $value != $this->value);

			$checked = (string) $option['checked'];
			$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			$selected = (string) $option['selected'];
			$selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

			$tmp = array(
				'value'    => $value,
				'text'     => \JText::alt($text, $fieldname),
				'disable'  => $disabled,
				'class'    => (string) $option['class'],
				'selected' => ($checked || $selected),
				'checked'  => ($checked || $selected)
			);

			// Add the option object to the result set.
			$options[] = (object) $tmp;
		}

		reset($options);

		return $options;
	}

	/**
	 * Method to set (replace) list options
	 *
	 * @param array $options
	 *
	 *
	 * @since 1.0
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $option)
		{
			if (!is_array($option))
			{
				$this->appendOption($option, $option);
			}
			else
			{
				$key      = (isset($option['value']) ? $option['value'] : $option['text']);
				$value    = $option['text'];
				$selected = (isset($option['selected']) ? $option['selected'] : false);
				$disabled = (isset($option['disabled']) ? $option['disabled'] : false);
				$class    = (isset($option['class']) ? $option['class'] : '');
				$this->appendOption($key, $value, $selected, $disabled, $class);
			}
		}
	}

	/**
	 * Method to add option to list end
	 *
	 * @param        $key
	 * @param        $value
	 * @param bool   $selected
	 * @param bool   $disabled
	 * @param string $class
	 *
	 *
	 * @since 1.0
	 */
	public function appendOption($key, $value, $selected = false, $disabled = false, $class = '')
	{
		$this->_options[] = array('value' => $key, 'text' => $value, 'selected' => $selected, 'checked' => $selected, 'disabled' => $disabled, 'class' => $class);
	}

}