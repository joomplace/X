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
$link  = $displayData['link'];
$class = $displayData['class'];

\JHtml::_('behavior.framework');
\JHtmlGrid::behavior();
?>
<a href="<?php echo $link; ?>" class="btn btn-<?php echo $class ?>"
   title="<?php echo \Joomplace\Library\JooYii\Helpers\JYText::_('LIST_CONTROL_BTN_TITLE_' . strtoupper($value)); ?>">
	<?php echo \Joomplace\Library\JooYii\Helpers\JYText::_('LIST_CONTROL_BTN_' . strtoupper($value)) ?>
</a>