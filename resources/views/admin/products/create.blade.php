@extends('layouts.admin')

@section('title', 'Add product')

@section('content')
<div class="page-header">
    <h1>Add product</h1>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @include('admin.products._form')
</form>
@endsection
