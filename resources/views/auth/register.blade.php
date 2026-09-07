@extends('layouts.app')

@section('title', 'Sign up')

@section('content')
<div class="auth-wrapper">
    <h1>Create your account</h1>
    <p class="subtitle">Join My-set to start shopping.</p>

    <div class="form-card">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create account</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</div>
@endsection
