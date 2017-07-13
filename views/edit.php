<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 22.06.2017
 * Time: 15:11
 */
defined('_JEXEC') or die;

$view = $displayData;

$input = JFactory::getApplication()->input;
JToolbarHelper::title(JText::_(strtoupper($input->get('option')).'_BRAND').JText::_(strtoupper($input->get('option')).'_'.strtoupper(JText::_($input->get('view'))).'_'.($input->get('id')?'EDIT':'CREATE').'_HEADER'), 'article');
JToolbarHelper::apply();
JToolbarHelper::save();
$key = $view->item->getKeyName();
if($view->item->$key){
    JToolbarHelper::save2copy();
}
JToolbarHelper::save2new();
JToolbarHelper::cancel();

$form = $view->item->getForm();
?>
<form id="adminForm" name="adminForm" class="adminForm" method="POST">
    <?php
    foreach ($form->getFieldset() as $field){
        /** @var JFormField $field */
        echo $field->renderField();
    }
    ?>
    <input type="hidden" name="option" value="<?= $input->get('option') ?>">
    <input type="hidden" name="view" value="<?= $input->get('view') ?>">
    <input type="hidden" name="task" value="">
</form>