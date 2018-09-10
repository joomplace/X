@extends('crud.form')
@php
$method = 'put';
@endphp
@section('toolbar')
    @php
        $view = $this->getGlobal('view');
    @endphp
    @jtoolbar('save',[$view.'.update'])
@endsection
