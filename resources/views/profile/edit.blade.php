@extends('layouts.app')

@section('title', 'My account')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>My account</h1>
        <p class="subtitle">Keep your contact details up to date so checkout is one step faster.</p>
    </div>

    <div class="cart-layout">
        <div class="form-card">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="name">Full name</label>
                    <input type="text" id="name" value="{{ $user->name }}" disabled>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled>
                </div>

                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 555 123 4567">
                    <div class="field-error">@error('phone'){{ $message }}@enderror</div>
                </div>

                <div class="form-group">
                    <label for="address">Shipping address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="Street, city, postal code">
                    <div class="field-error">@error('address'){{ $message }}@enderror</div>
                </div>

                <button type="submit" class="btn btn-primary">Save changes</button>
            </form>
        </div>

        <div class="summary-card">
            <h3>Why save this?</h3>
            <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                Your phone number and address will automatically fill in at checkout, so you don't have to retype them on every order.
            </p>
        </div>
    </div>
</div>
@endsection
