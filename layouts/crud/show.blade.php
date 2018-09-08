@php
    /** @var \Joomplace\X\Model $item */
@endphp
@foreach($item->getAttributes() as $column => $value)
    <label>@lang(strtoupper($item->getTable()).'_'.strtoupper($column))</label>
    <div style="min-height: 1rem;">
        {{$value}}
    </div>
@endforeach