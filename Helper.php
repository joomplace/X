<?php
/**
 * @package     Joomplace\Library\JooYii
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii;

use ReflectionMethod;
use JText;
use JFile;

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
	public static function getClassName($obj, $short = true)
	{
		$class = get_class($obj);
		if (!isset(self::$_classreflections[$class]))
		{
			self::saveClassReflection($obj);
		}
		if($short){
			return self::$_classreflections[$class]->getShortName();
		}else{
			return self::$_classreflections[$class]->getName();
		}
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

	public static function methodExists($class, $method, $in_target = false){
		if(method_exists($class,$method)){
			if($in_target){
				$ref = new ReflectionMethod($class, $method);
				if(strtolower($ref->getDeclaringClass()->name) == strtolower($class)){
					return true;
				}else{
					return false;
				}
			}else{
				return true;
			}

		}else{
			return false;
		}
	}

	/**
	 * @param $class
	 * @param $method
	 *
	 * @return \ReflectionParameter[]
	 */
	public static function getMethodArgs($class, $method) {
		if(static::methodExists($class, $method)){
			$ref = new ReflectionMethod($class, $method);
			$result = $ref->getParameters();
		}else{
			$result = array();
		}
		return $result;
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
		foreach (static::getMethodArgs($class, $method) as $param)
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
					throw new \Exception("Need to define $param->name", 500);
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

	public static function reveal($obj){
		return array_filter(get_object_vars($obj), function ($key)
		{
			if (strpos($key, '_') === 0)
			{
				return false;
			}
			else
			{
				return true;
			}
		}, ARRAY_FILTER_USE_KEY);
	}

	public static function uploadFile(array $file, $path,array $validFileTypes = array(),array $validFileExts = array()){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$fileError = $file['error'];
		if ($fileError > 0)
		{
			switch ($fileError)
			{
				case 1:
					echo JText::_( 'FILE TO LARGE THAN PHP INI ALLOWS' );
					return;

				case 2:
					echo JText::_( 'FILE TO LARGE THAN HTML FORM ALLOWS' );
					return;

				case 3:
					echo JText::_( 'ERROR PARTIAL UPLOAD' );
					return;

				case 4:
					echo JText::_( 'ERROR NO FILE' );
					return;
			}
		}

		$fileSize = $file['size'];
		if($fileSize > 20000000)
		{
			echo JText::_( 'FILE BIGGER THAN 20MB' );
		}

		$fileName = $file['name'];
		$uploadedFileNameParts = explode('.',$fileName);
		$uploadedFileExtension = array_pop($uploadedFileNameParts);

		$extOk = true;
		if($validFileExts){
			$extOk = false;
			foreach($validFileExts as $key => $value)
			{
				if( preg_match("/$value/i", $uploadedFileExtension ) )
				{
					$extOk = true;
				}
			}
		}

		if ($extOk == false)
		{
			echo JText::_( 'INVALID EXTENSION' );
			return;
		}

		$fileTemp = $file['tmp_name'];

		$invalidMime = false;
		if($validFileTypes && !in_array(mime_content_type($fileTemp), $validFileTypes)){
			$invalidMime = true;
		}

		//lose any special characters in the filename
		$fileName = preg_replace("/[^A-Za-z0-9\\.]/i", "-", $fileName);

		$uploadPath = JPATH_SITE . '/'.$path;
		$uploadPath = '/'.trim($uploadPath,'/\\').'/'.$fileName;

		if(is_file($uploadPath)){
			echo JText::_( 'File '.$fileName.' already exists' );
			return false;
		}
		if(!JFile::upload($fileTemp, $uploadPath))
		{
			echo JText::_( 'ERROR MOVING FILE' );
			return false;
		}
		else
		{
			return str_replace(JPATH_SITE,'',$uploadPath);
		}
	}

	public static function moveFile($from, $to){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		return JFile::move($from,$to);
	}

	public static function isJson($string) {
		return ((is_string($string) &&
			(is_object(json_decode($string)) ||
				is_array(json_decode($string))))) ? true : false;
	}
}
