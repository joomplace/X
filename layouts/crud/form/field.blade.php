@php
    /** @var \Joomplace\X\Model $item */
@endphp

<label>@lang($item->getLabelFor($field))</label>
<input type="text" name="{{$field}}" placeholder="@lang($item->getPlaceholderFor($field))" value="{{$item->$field}}"/>