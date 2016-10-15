<?php
/**
 * @package     Joomplace\Library\JooYii
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

use ReflectionMethod;

/**
 * Helper class for common good things
 *
 * @package     Joomplace\Library\JooYii
 *
 * @since       1.0
 */
class Helper
{
	/**
	 * Store for reflections
	 * @var array Array of reflections
	 * @since 1.0
	 */
	protected static $_classreflections = array();

	/**
	 * Extracts class name of passed object
	 * without namespace
	 *
	 * @param $obj Object of class
	 *
	 * @return mixed Class name
	 *
	 * @since 1.0
	 */
	public static function getClassName($obj)
	{
		$class = get_class($obj);
		if (!isset(self::$_classreflections[$class]))
		{
			self::saveClassReflection($obj);
		}

		return self::$_classreflections[$class]->getShortName();
	}

	/**
	 * Stores static cache of reflections
	 *
	 * @param $obj
	 *
	 *
	 * @since 1.0
	 */
	protected static function saveClassReflection($obj)
	{
		$class                           = get_class($obj);
		self::$_classreflections[$class] = new \ReflectionClass($obj);
	}

	/**
	 * Extracts namespace/.. of passed object
	 *
	 * @param $obj Object of class
	 *
	 * @return mixed Namespace
	 *
	 * @since 1.0
	 */
	public static function getClassParentNameSpacing($obj)
	{
		$class = get_class($obj);
		if (!isset(self::$_classreflections[$class]))
		{
			self::saveClassReflection($obj);
		}

		return substr(self::$_classreflections[$class]->getNamespaceName(), 0, strrpos(self::$_classreflections[$class]->getNamespaceName(), '\\'));
	}

	/**
	 * Extracts namespace of passed object
	 *
	 * @param $obj Object of class
	 *
	 * @return mixed Namespace
	 *
	 * @since 1.0
	 */
	public static function getClassNameSpace($obj)
	{
		$class = get_class($obj);
		if (!isset(self::$_classreflections[$class]))
		{
			self::saveClassReflection($obj);
		}

		return self::$_classreflections[$class]->getNamespaceName();
	}

	/**
	 * Use for smart text trimming
	 *
	 * @param string $text   Input
	 * @param int    $length Max length
	 *
	 * @return string Trimmed string
	 *
	 * @since 1.0
	 */
	public static function trimText($text, $length = 35)
	{
		if (strlen($text) > $length)
		{
			$array = explode("|||", wordwrap($text, $length, "|||"));

			return array_shift($array) . "...";
		}
		else
		{
			return $text;
		}
	}

	/**
	 * Use for calling methods on classes
	 * with auto binding of method params from passed inputs
	 * in "first in is king" priority
	 *
	 * @param       $class  Class name or Object
	 * @param       $method Class method name
	 * @param array $inputs Array of \JRegestry|\JInput objects
	 *
	 * @return bool|mixed Function response
	 *
	 * @since 1.0
	 */
	public static function callBindedFunction($class, $method, $inputs = array())
	{

		if (!$inputs)
		{
			$inputs = array(
				\JFactory::getApplication()->input,
			);
		}

		$arguments = array();
		$ref       = new ReflectionMethod($class, $method);
		foreach ($ref->getParameters() as $param)
		{
			/** @var \ReflectionParameter $param */
			/*
			 * TODO: set initial filter from config
			 */
			$filter = 'RAW';
			if ($param->isArray())
			{
				$filter = 'array';
			}
			foreach ($inputs as $input)
			{
				if (!($input instanceof \Joomla\Registry\Registry || $input instanceof \JInput))
				{
					throw new \InvalidArgumentException('Input must be instanceof JRegestry');

					return false;
				}
				$value = $input->get($param->name, null, $filter);
				if ($value !== null)
				{
					break;
				}
			}
			if ($value === null)
			{
				if (!$param->isDefaultValueAvailable())
				{
					trigger_error("Need to define $param->name", E_USER_ERROR);
				}
				else
				{
					$value = $param->getDefaultValue();
				}
			}
			$arguments[] = $value;
		}

		return call_user_func_array(array($class, $method), $arguments);
	}

	/**
	 * Use for generating captcha field
	 * Pass $value for triggering validation process
	 *
	 * @param null $value Captcha response
	 *
	 * @return \JFormFieldCaptcha Captcha form field
	 *
	 * @since 1.0
	 */
	public static function getCaptcha($value = null)
	{
		ob_start()
		?>
		<field
			namespace="form"
			name="captcha"
			type="captcha"
			label="CAPTCHA_LABEL"
			description="CAPTCHA_DESC"
			validate="captcha"
		/>
		<?php
		$xml_element = ob_get_contents();
		ob_end_clean();
		$sxmle = new \SimpleXMLElement($xml_element);

		$form_el = new \JFormFieldCaptcha();
		$form_el->setup($sxmle, $value);

		if ($value)
		{
			$form           = \JForm::getInstance('captcha_pseudo', '<form><fieldset>' . $xml_element . '</fieldset></form>');
			$form_el->valid = $form->validate(array('captcha' => $value));
		}

		return $form_el;
	}
}