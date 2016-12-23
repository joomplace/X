<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 * @blameThem   https://github.com/joomla/joomla-cms/pull/13264
 */

namespace Joomplace\Library\JooYii\Helpers;


class JYText extends \JText
{
	protected static $_strings = array();
	
	public static function def($key, $value){
		if(!isset(static::$_strings[$key])){
			static::$_strings[$key] = $value;
		}
	}
	
	protected static function decider($string){
		if(\JFactory::getLanguage()->hasKey($string) || !isset(static::$_strings[$string]))
		{
			return $string;
		}else{
			return static::$_strings[$string];
		}
	}

	public static function sprintf($string)
	{
		return parent::sprintf(self::decider($string));
	}

	public static function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
	{
		return parent::script(self::decider($string), $jsSafe, $interpretBackSlashes);
	}

	public static function printf($string)
	{
		return parent::printf(self::decider($string));
	}

	public static function plural($string, $n)
	{
		return parent::plural(self::decider($string), $n);
	}

	public static function alt($string, $alt, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return parent::alt(self::decider($string), $alt, $jsSafe, $interpretBackSlashes, $script);
	}

	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return parent::_(self::decider($string), $jsSafe, $interpretBackSlashes, $script);
	}
}