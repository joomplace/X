<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Layouts
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

defined('JPATH_PLATFORM') or die;

$value = $displayData;

if ($value)
{
	?>
	<span class="icon-publish"></span>
	<?php
}
else
{
	?>
	<span class="icon-unpublish"></span>
	<?php
}