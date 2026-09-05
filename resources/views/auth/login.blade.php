@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<div class="auth-wrapper">
    <h1>Welcome back</h1>
    <p class="subtitle">Log in to continue shopping.</p>

    <div class="form-card">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
        <p class="auth-switch" style="margin-top:6px; font-size:0.8rem;">Demo admin: admin@example.com / password</p>
    </div>
</div>
@endsection
