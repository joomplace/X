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
$csspath = JHtml::_('stylesheet', 'jooyii/ddupload.min.css', false, true, true);
if($csspath){
	JHtml::_('stylesheet', 'jooyii/ddupload.min.css', false, true);
}else{
	JFactory::getDocument()->addStyleSheetVersion(JUri::root(true).'/libraries/JooYii/Layouts/form/css/ddupload.min.css');
}
$jspath = JHtml::_('script', 'jooyii/ddupload.min.js', false, true, true);
if($jspath){
	JHtml::_('script', 'jooyii/ddupload.min.js', false, true);
}else{
	JFactory::getDocument()->addScript(JUri::root(true).'/libraries/JooYii/Layouts/form/js/ddupload.min.js');
}
?>
<div data-upurl="<?= $ajax_url ?>" class="ddupload_field">
	<!-- D&D Zone-->
	<label>
		<?= $label ?>
	</label>
	<div class="uploader">
		<div>Drag &amp; Drop Images Here</div>
		<div class="or">-or-</div>
		<div class="browser">
			<label>
				<span>Click to open the file Browser</span>
				<input type="file" name="files_<?= $fieldname ?>[]" multiple="<?= $multiple ?>" title="<?= $description ?>">
				<input type="hidden" required="<?= $required ?>" name="<?= $name ?>" multiple="<?= $multiple ?>" class="thumbsData">
			</label>
		</div>
	</div>
	<!-- /D&D Zone -->
	<div class="files_container">
	</div>
</div>
