<?php
Joomla\CMS\HTML\HTMLHelper::_('behavior.core');
?>

@section('fields')
    <?php
    /** @var \Joomplace\X\Model $item */
    $key = $item->getKeyName();
    ?>
    <input type="hidden" name="{{$key}}" value="{{$item->$key}}">
    @foreach($item->getFillable() as $field)
        @hasSection('field.'.$field)
            @yield('field.'.$field)
        @else
            @include('crud.form.field',['item'=>$item,'field'=>$field])
        @endif
    @endforeach
@endsection

@section('form')
    @yield('fields')
@endsection

@yield('toolbar')
<form method="<?= $method ?>" name="adminForm" id="adminForm" action="<?= Joomla\CMS\Router\Route::_('index.php') ?>">
    <input name="option" value="<?= \Joomla\CMS\Factory::getApplication()->input->get('option') ?>" type="hidden">
    <input name="task" value="" type="hidden">
    <input name="httpxmethod" value="<?= $method ?>" type="hidden">
    @yield('form')
</form>