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
?>
<div class="file_entry">
	<i class="icon-file"><?= $file ?></i>
	<p><?= $file_name ?></p>
</div>
