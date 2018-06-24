<?php
/** @var Illuminate\Pagination\LengthAwarePaginator $items */
use Joomla\CMS\Pagination\Pagination;

Joomla\CMS\HTML\HTMLHelper::_('behavior.core');

$modelClass = ($items->getIterator()->current()->getMorphClass());
if($columns==null){
    $columns = $items->getIterator()->current()->getFillable();
}
?>
@can('create',$modelClass)
    @php
        JToolbarHelper::addNew('create');
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
                    {{$items->getIterator()->current()->getLabelFor($column)}}
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
                @if($item::can('view',$item))
                   href="{{\Joomla\CMS\Router\Route::_('index.php?option=com_xexamples&id='.$item->id,false)}}"
                @else
                   href="javascript:void(0);"
                   disabled="true"
                @endif
                >
                    show
                </a>
                <a class="btn btn-small"
                @if($item::can('view',$item))
                    href="{{\Joomla\CMS\Router\Route::_('index.php?option=com_xexamples&task=edit&id='.$item->id,false)}}"
                @else
                    href="javascript:void(0);"
                    disabled="true"
                @endif
                >
                    edit
                </a>
                <form method="delete" style="display: inline;margin: 0px;">
                    @if($item::can('view',$item))
                        <input name="option" value="com_xexamples" type="hidden">
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
                    <input name="option" value="com_xexamples" type="hidden">
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