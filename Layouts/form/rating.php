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
JHtml::_('jquery.framework');
echo "<pre>";
print_r($displayData);
echo "</pre>";
