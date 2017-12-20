@php
    /** @var \Joomplace\X\Model $item */
@endphp
@section('field')
    <label>@lang($item->getLabelFor($field))</label>
    <input type="text" name="{{$filed}}" placeholder="@lang($item->getPlaceholderFor($field))"
           value="{{$item->$field}}"/>
@endsection

@yield('field')