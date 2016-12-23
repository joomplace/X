<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Fields
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

namespace Joomplace\Library\JooYii\Fields;

use Joomplace\Library\JooYii\Helper;
use Joomplace\Library\JooYii\Loader;

jimport('joomla.form.helper');
\JFormHelper::loadFieldClass('list');
\JFormHelper::loadFieldClass('text');
/**
 * Dynamic list field type
 *
 * @package     Joomplace\Library\JooYii\Fields
 *
 * @since       1.0
 */
class Rating extends \JFormFieldText
{
	/**
	 * Fully qualified class name
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $type = '\\Joomplace\\Library\\JooYii\\Fields\\Rating';

	public function _getInput(){
		list($def_path) = Loader::getPathByPsr4('Joomplace\\Library\\JooYii\\Layouts\\', '/');
		$params = array();
		foreach ($this as $k => $item){
			if(!is_array($item)){
				$params[$k] = $item;
			}
		}
		$html = \JLayoutHelper::render('form.rating', $params, $def_path);
		return $html;
	}

}