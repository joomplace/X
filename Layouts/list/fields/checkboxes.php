<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Layouts
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

defined('JPATH_PLATFORM') or die;
extract($displayData);
if($value){
	echo "<ul>";
	foreach ($value as $v){
		echo "<li>$v</li>";
	}
	echo "</ul>";
}else{
	echo JText::_('NO_VALUE');
}
