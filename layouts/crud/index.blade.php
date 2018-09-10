<?php
/** @var Illuminate\Pagination\LengthAwarePaginator $items */
use Joomla\CMS\Pagination\Pagination;

Joomla\CMS\HTML\HTMLHelper::_('behavior.core');

$option = \Joomla\CMS\Factory::getApplication()->input->get('option');
$model = new $modelClass;
if($columns==null){
    $columns = $model->getFillable();
}
?>
@can($this->getGlobal('view').'.create',$modelClass)
    @php
        JToolbarHelper::addNew($this->getGlobal('view').'.create');
    @endphp
@endcan
@section('content-table')
<table class="table table-bordered">
    <thead>
        <tr>
            <th>
                @lang('JOOMPLACE_X_GRID_ID')
            </th>
            <?php foreach($columns as $column){ ?>
                <th>
                    {{$model->getLabelFor($column)}}
                </th>
            <?php } ?>
            <th>
                @lang('JOOMPLACE_X_GRID_ACTIONS')
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($items->all() as $item)
        <tr>
            <td>
                {{$item->id}}
            </td>
            <?php foreach($columns as $column){ ?>
                <td>
                    {{$item->$column}}
                </td>
            <?php } ?>
            <td>
                <a class="btn btn-small"
                   @if($item::can($this->getGlobal('view').'.view',$item))
                   href="{{\Joomla\CMS\Router\Route::_('index.php?option='.$option.'&view='.$this->getGlobal('view').'&id='.$item->id,false)}}"
                   @else
                   href="javascript:void(0);"
                   disabled="true"
                @endif
                >
                    show
                </a>
                <a class="btn btn-small"
                   @if($item::can($this->getGlobal('view').'.view',$item))
                   href="{{\Joomla\CMS\Router\Route::_('index.php?option='.$option.'&task='.$this->getGlobal('view')
                    .'.edit&id='.$item->id, false)}}"
                   @else
                    href="javascript:void(0);"
                   disabled="true"
                @endif
                >
                    edit
                </a>
                <form method="delete" style="display: inline;margin: 0px;">
                    @if($item::can($this->getGlobal('view').'.delete',$item))
                        <input name="option" value="{{$option}}" type="hidden">
                        <input name="view" value="{{$this->getGlobal('view')}}" type="hidden">
                        <input name="id" value="{{$item->id}}" type="hidden">
                        <input name="httpxmethod" value="delete" type="hidden">
                        <button type="submit" class="btn btn-small">delete</button>
                    @else
                        <button type="submit" class="btn btn-small" disabled="true">delete</button>
                    @endif
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">
                <?php $jpagination = new Pagination($items->total(),($items->currentPage()-1)*$items->perPage(),
                    $items->perPage()); ?>
                <form method="get" name="adminForm" id="adminForm" action="<?= Joomla\CMS\Router\Route::_('index.php') ?>">
                    <input name="option" value="{{$option}}" type="hidden">
                    <input name="task" value="" type="hidden">
                    <?= $jpagination->getListFooter() ?>
                </form>
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endsection

@yield('content-table')