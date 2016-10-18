<?php
/**
 * @package     Joomplace\Library\JooYii
 * @subpackage  Joomplace\Library\JooYii\Layouts
 *
 * @copyright   Alexandr Kosarev
 * @license     GPL2
 */

defined('JPATH_PLATFORM') or die;

$value = $displayData['value'];
$task  = $displayData['task'];
$id    = $displayData['id'];
$class = $displayData['class'];

\JHtml::_('behavior.framework');
\JHtmlGrid::behavior();
?>
<a href="#" class="btn btn-<?php echo $class ?>"
   onclick="return listItemTask('cb<?php echo $id ?>','<?php echo $task ?>')"
   title="<?php echo JText::_('LIST_CONTROL_BTN_TITLE_' . strtoupper($value)); ?>">
	<?php echo JText::_('LIST_CONTROL_BTN_' . strtoupper($value)) ?>
</a>