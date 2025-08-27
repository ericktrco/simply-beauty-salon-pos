@extends('backend.layouts.app')
@section('title')  {{ __($module_title) }} @endsection

@push('after-styles')
<link rel="stylesheet" href='{{ mix("modules/constant/style.css") }}'>
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush
@section('content')

    <div data-render="app">
        <billing-page></billing-page>
    </div>
@endsection


@push('after-scripts')
<!-- DataTables JS -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src='{{ mix("modules/billing/script.js") }}'></script>
@endpush 