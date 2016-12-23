<?php
defined('_JEXEC') or die;
extract($displayData);
/** @var \Joomplace\Library\JooYii\Model $item */
JHtml::_('behavior.formvalidator');
$form = $item->getForm();
?>
<form id="adminForm" name="adminForm" action="<?php echo $form_action; ?>" class="adminForm validate-form" method="POST">
	<div class="form-horizontal">
		<?php
		$fieldsets = $form->getFieldsets();
		echo JHtml::_('bootstrap.startTabSet', 'editTabs', array('active' => key($fieldsets)));
		foreach ($fieldsets as $fieldset){
			echo JHtml::_('bootstrap.addTab', 'editTabs', $fieldset->name, \Joomplace\Library\JooYii\Helpers\JYText::_($fieldset->label));
			foreach ($form->getFieldset($fieldset->name) as $field){
				/** @var JFormField $field */
				echo $field->renderField();
			}
			echo JHtml::_('bootstrap.endTab');
		}
		echo JHtml::_('bootstrap.endTabset');
		?>
		<?php foreach ($form_params as $k => $p){
			?>
			<input type="hidden" name="<?php echo $k ?>" value="<?php echo $p ?>">
			<?php
		} ?>
	</div>
</form>