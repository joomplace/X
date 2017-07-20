<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 18.07.2017
 * Time: 21:57
 */

echo JHtml::_('bootstrap.addTab', $displayData->set, $displayData->name, JText::_($displayData->label?$displayData->label:('FIELDSET_'.strtoupper($displayData->name))));
foreach ($displayData->fields as $field){
    /** @var JFormField $field */
    echo $field->renderField();
}
echo JHtml::_('bootstrap.endTab');
