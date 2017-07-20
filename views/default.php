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
JToolbarHelper::title(JText::_(strtoupper($input->get('option')).'_BRAND').JText::_(strtoupper($input->get('option')).'_'.strtoupper($input->get('view')).'_HEADER'), 'stack article');

/** @var JViewLegacy $view */
if($rows = $view->items){
    JToolbarHelper::deleteList(JText::_(strtoupper($input->get('option')).'_LIST_SURE_U_WANT_TO_DELETE'),$input->get('view').'.delete');
}
JToolbarHelper::link('index.php?option='.$input->get('option').'&view='.$input->get('view').'&task=add',JText::_(strtoupper($input->get('option')).'_'.strtoupper($input->get('view')).'_CREATE'),'new');

if($state = $view->state){
    $view->listOrder = $state->get('list.ordering');
    $view->listDirn  = $state->get('list.direction');
}else{
    $view->listOrder = 'id';
    $view->listDirn = 'asc';
}
$saveOrder = $view->listOrder == 'ordering';
$view->saveOrder = $saveOrder;

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option='.$input->get('option').'&controller='.$input->get('view').'&task=saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', $input->get('view').'List', 'adminForm', strtolower($view->listDirn), $saveOrderingUrl);
}
?>
<form id="adminForm" name="adminForm" class="adminForm" method="POST">
    <?php if (!empty( $view->sidebar)){ ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $view->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php }else{ ?>
        <div id="j-main-container">
    <?php } ?>
        <?php
        $columns = $view->columns;
        /** @var \JPagination $pagination */
        $pagination = $view->pagination;
        ?>
        <table id="<?php echo $input->get('view'); ?>List" class="table table-bordered table-striped">
            <thead>
            <tr>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHTML::_('grid.sort', '<span class="icon-menu-2"></span>', 'ordering', $view->listDirn, false); ?>
                </th>
                <th width="1%" class="center">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th width="1%" class="center">

                </th>
                <?php
                foreach ($columns as $column){
                    ?>
                    <th>
                        <?php echo JHTML::_('grid.sort', strtoupper($input->get('option')).'_'.strtoupper($input->get('view')).'_LIST_HEAD_'.strtoupper($column), $column, $view->listDirn, $view->listOrder); ?>
                    </th>
                    <?php
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            /**
             * @var int $i
             * @var \JoomPlaceX\Model $row
             */
            foreach ($rows as $i => $row){
                ?>
                <tr class="row<?php echo $i % 2; ?>" <?php /* use if grouped ?>sortable-group-id="<?php echo $item->catid; ?>" <?php */ ?>>
                    <td class="order nowrap center hidden-phone">
                        <?php
                        $iconClass = '';
                        if (!$view->saveOrder)
                        {
                            $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                        }
                        ?>
                        <span class="sortable-handler<?php echo $iconClass ?>">
                                    <span class="icon-menu"></span>
                                </span>
                        <?php if ($view->saveOrder) : ?>
                            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order " />
                        <?php endif; ?>
                    </td>
                    <td class="center">
                        <?= JHtml::_('grid.id', $row->id, $row->id); ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                            <?= JHtml::_('jgrid.action', $row->id, 'edit', $input->get('view').'.', '', '','',false,'edit') ?>
                            <?= JHtml::_('jgrid.published', $row->published, $row->id, $input->get('view').'.', $canChange = true, 'cb'/*, $row->publish_up, $row->publish_down*/); ?>
                        </div>
                    </td>
                    <?php
                    foreach ($columns as $column){
                        ?>
                        <td>
                            <?php
                            echo $row->renderListControl($column);
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="2">
                    <?php echo $pagination->getLimitBox(); ?>
                </td>
                <td class="text-right" colspan="<?php echo count($columns) + 1; ?>">
                    <?php echo $pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="filter_order" value="<?php echo $view->listOrder; ?>">
        <input type="hidden" name="filter_order_Dir" value="<?php echo $view->listDirn; ?>">
        <input type="hidden" name="option" value="<?= $input->get('option') ?>">
        <input type="hidden" name="controller" value="<?= $input->get('view') ?>">
        <input type="hidden" name="task" value="index">
        <input type="hidden" name="boxchecked" value="">
    </div>
</form>