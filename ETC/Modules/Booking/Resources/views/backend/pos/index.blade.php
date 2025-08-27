@extends('backend.layouts.app')

@section('title') POS @endsection

@section('content')
<div class="card">
  <div class="card-body">
    <div data-render="app">
      <walk-in-pos :initial-branch='@json(["id"=>$initialBranch->id,"name"=>$initialBranch->name])'></walk-in-pos>
    </div>
  </div>
</div>
@endsection

@push('after-scripts')
<script src="{{ mix('modules/booking/script.js') }}"></script>
@endpush


