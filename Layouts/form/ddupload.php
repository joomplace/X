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
$values = json_decode($value);
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
				<input type="file" name="files_<?= $fieldname ?>[]" <?= $multiple?'data-multiple="true"':'' ?> title="<?= $description ?>">
				<input type="hidden" required="<?= $required ?>" name="<?= $name ?>" <?= $multiple?'data-multiple="true"':'' ?> value="<?=  empty($values)?'':(implode('|', $values)); ?>" class="thumbsData">
			</label>
		</div>
	</div>
	<!-- /D&D Zone -->
	<div class="files_container">
		<?php if ($values) {
			$i = 0;
			foreach($values as $value) {
				$value = explode('/', $value);
				$cell = count($value) - 1;
				?>
				<div class="file_outter">
					<div class="file_close" data-file="<?= $i++ ?>">
						&times
					</div>
					<div class="file_entry">
						<i class="icon-file"></i>
						<p><?= $value[$cell] ?></p>
					</div>
				</div>

		<?php
		}
		}
		?>
	</div>
</div>
