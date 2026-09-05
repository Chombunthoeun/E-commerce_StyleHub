@extends('layouts.admin')

@section('title', 'Edit product')

@section('content')
<div class="page-header">
    <h1>Edit product</h1>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @include('admin.products._form')
</form>
@endsection
