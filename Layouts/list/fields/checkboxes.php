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

echo "<ul>";
foreach ($value as $v){
	echo "<li>$v</li>";
}
echo "</ul>";