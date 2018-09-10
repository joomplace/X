@extends('crud.form')
@php
$method = 'post';
@endphp
@section('toolbar')
    @php
        $view = $this->getGlobal('view');
    @endphp
    @jtoolbar('save',[$view.'.store'])
@endsection
