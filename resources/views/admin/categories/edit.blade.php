@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
<div class="page-header">
    <h1>Edit category</h1>
</div>

<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="panel" style="max-width:480px;">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Category name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required autofocus>
        <div class="field-error">@error('name'){{ $message }}@enderror</div>
    </div>
    <div style="display:flex; gap:12px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection
